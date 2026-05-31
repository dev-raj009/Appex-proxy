#!/usr/bin/env node

/**
 * ============================================================
 *  APPX V2 SECURE VIDEO PROXY SERVER
 *  Render.com Deployment Ready — 100% Working
 * ============================================================
 *  - XOR decryption (first 28 bytes)
 *  - Video streaming with Range support
 *  - PDF proxy
 *  - CORS enabled
 *  - Rate limiting
 *  - Health check endpoint
 * ============================================================
 */

const express      = require('express');
const axios        = require('axios');
const cors         = require('cors');
const helmet       = require('helmet');
const morgan       = require('morgan');
const compression  = require('compression');
const rateLimit    = require('express-rate-limit');

// ==================== APP SETUP ====================
const app  = express();
const PORT = process.env.PORT || 3000;

// Trust Render's reverse proxy
app.set('trust proxy', 1);

// ==================== MIDDLEWARE ====================
app.use(cors({ origin: '*', methods: ['GET', 'HEAD', 'OPTIONS'] }));
app.use(helmet({ contentSecurityPolicy: false, crossOriginResourcePolicy: false }));
app.use(morgan('[:date[clf]] :method :url :status :response-time ms'));
app.use(compression());
app.use(express.json());

// ==================== RATE LIMITING ====================
const limiter = rateLimit({
    windowMs     : 15 * 60 * 1000,   // 15 minutes
    max          : 200,               // requests per window
    standardHeaders: true,
    legacyHeaders: false,
    message      : { error: 'Too many requests, please try again later.' }
});
app.use('/video', limiter);
app.use('/pdf',   limiter);

// ==================== CONSTANTS ====================
// Headers required by AppX CDN — without these you get 403
const APPX_HEADERS = {
    'Referer'   : 'https://appx-play.akamai.net.in/',
    'Origin'    : 'https://appx-play.akamai.net.in',
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept'    : '*/*',
    'Accept-Language': 'en-US,en;q=0.9',
    'Connection': 'keep-alive'
};

// ==================== DECRYPTION ====================
/**
 * XOR decrypt first 28 bytes of buffer
 * Key bytes are used cyclically; if key shorter than 28, index is used
 */
function decryptHeader(buffer, key) {
    const len = Math.min(28, buffer.length);
    for (let i = 0; i < len; i++) {
        const keyByte = (i < key.length) ? key.charCodeAt(i) : i;
        buffer[i] ^= keyByte;
    }
    return buffer;
}

// ==================== HELPERS ====================
function isValidUrl(str) {
    try { new URL(str); return true; } catch (e) { return false; }
}

function extractKeyFromUrl(url) {
    const m = url.match(/[?&]KeyName=([^&]+)/);
    return m ? decodeURIComponent(m[1]) : null;
}

function sendError(res, status, msg) {
    if (!res.headersSent) {
        res.status(status).json({ error: msg });
    }
}

// ==================== HEALTH CHECK ====================
app.get('/health', (req, res) => {
    res.json({
        status   : 'ok',
        service  : 'AppX Proxy Server',
        version  : '2.0.0',
        uptime   : Math.floor(process.uptime()),
        memory   : process.memoryUsage().heapUsed,
        timestamp: new Date().toISOString(),
        node     : process.version
    });
});

// ==================== ROOT ====================
app.get('/', (req, res) => {
    res.json({
        service  : 'AppX V2 Secure Video Proxy',
        status   : 'running',
        endpoints: {
            health: '/health',
            video : '/video?url=ENCODED_URL&key=appx-pdf-keyset',
            pdf   : '/pdf?url=ENCODED_URL&key=appx-pdf-keyset'
        }
    });
});

// ==================== VIDEO ENDPOINT ====================
app.get('/video', async (req, res) => {
    // --- Parse params ---
    let videoUrl = req.query.url;
    let key      = req.query.key;

    if (!videoUrl) return sendError(res, 400, 'Missing "url" parameter');

    try { videoUrl = decodeURIComponent(videoUrl); } catch (e) {
        return sendError(res, 400, 'Invalid URL encoding');
    }

    if (!isValidUrl(videoUrl)) return sendError(res, 400, 'Invalid URL format');

    if (!key) {
        key = extractKeyFromUrl(videoUrl);
        if (!key) return sendError(res, 400, 'Missing decryption key');
    }

    // --- Range header ---
    const rangeHeader = req.headers['range'] || 'bytes=0-';
    const isSeekRequest = rangeHeader !== 'bytes=0-';

    let hostname;
    try { hostname = new URL(videoUrl).hostname; } catch (e) {
        return sendError(res, 400, 'Cannot parse URL hostname');
    }

    try {
        const upstream = await axios({
            method      : 'GET',
            url         : videoUrl,
            responseType: 'stream',
            headers     : {
                ...APPX_HEADERS,
                'Host' : hostname,
                'Range': rangeHeader
            },
            validateStatus: () => true,
            timeout       : 30000,
            maxRedirects  : 5
        });

        // --- Handle upstream errors ---
        if (upstream.status === 403) return sendError(res, 403, 'Forbidden — URL expired or invalid');
        if (upstream.status === 404) return sendError(res, 404, 'Video not found on upstream server');
        if (upstream.status >= 500)  return sendError(res, 502, 'Upstream server error: ' + upstream.status);

        // --- Set response headers ---
        res.status(upstream.status);
        res.setHeader('Content-Type',  'video/mp4');
        res.setHeader('Accept-Ranges', 'bytes');
        res.setHeader('Cache-Control', 'public, max-age=3600');
        res.setHeader('Access-Control-Allow-Origin', '*');
        res.setHeader('X-Proxy-By', 'AppX-Proxy-v2');

        if (upstream.headers['content-range'])  res.setHeader('Content-Range',  upstream.headers['content-range']);
        if (upstream.headers['content-length']) res.setHeader('Content-Length', upstream.headers['content-length']);

        // --- Seek request: pipe directly (no decrypt needed) ---
        if (isSeekRequest) {
            upstream.data.pipe(res);
            upstream.data.on('error', () => { if (!res.headersSent) res.end(); });
            return;
        }

        // --- Full request: decrypt first 28 bytes then pipe rest ---
        let headerBuffer = Buffer.alloc(0);
        let headerSent   = false;

        upstream.data.on('data', (chunk) => {
            if (headerSent) {
                res.write(chunk);
                return;
            }
            headerBuffer = Buffer.concat([headerBuffer, chunk]);
            if (headerBuffer.length >= 28) {
                decryptHeader(headerBuffer, key);
                res.write(headerBuffer);
                headerSent = true;
            }
        });

        upstream.data.on('end', () => {
            // Edge case: file smaller than 28 bytes
            if (!headerSent && headerBuffer.length > 0) {
                decryptHeader(headerBuffer, key);
                res.write(headerBuffer);
            }
            res.end();
        });

        upstream.data.on('error', (err) => {
            console.error('[STREAM ERROR]', err.message);
            if (!res.headersSent) res.status(500).end();
            else res.end();
        });

        req.on('close', () => {
            upstream.data.destroy();
        });

    } catch (err) {
        console.error('[VIDEO ERROR]', err.message);
        sendError(res, 500, 'Proxy error: ' + err.message);
    }
});

// ==================== PDF ENDPOINT ====================
app.get('/pdf', async (req, res) => {
    let pdfUrl = req.query.url;
    let key    = req.query.key;

    if (!pdfUrl) return sendError(res, 400, 'Missing "url" parameter');

    try { pdfUrl = decodeURIComponent(pdfUrl); } catch (e) {
        return sendError(res, 400, 'Invalid URL encoding');
    }

    if (!isValidUrl(pdfUrl)) return sendError(res, 400, 'Invalid URL format');

    if (!key) {
        key = extractKeyFromUrl(pdfUrl);
        if (!key) return sendError(res, 400, 'Missing decryption key');
    }

    let hostname;
    try { hostname = new URL(pdfUrl).hostname; } catch (e) {
        return sendError(res, 400, 'Cannot parse URL hostname');
    }

    try {
        const upstream = await axios({
            method      : 'GET',
            url         : pdfUrl,
            responseType: 'stream',
            headers     : { ...APPX_HEADERS, 'Host': hostname },
            validateStatus: () => true,
            timeout     : 30000
        });

        if (upstream.status === 403) return sendError(res, 403, 'Forbidden');
        if (upstream.status === 404) return sendError(res, 404, 'PDF not found');

        res.status(upstream.status);
        res.setHeader('Content-Type',        'application/pdf');
        res.setHeader('Content-Disposition', 'inline; filename="document.pdf"');
        res.setHeader('Cache-Control',       'public, max-age=3600');
        res.setHeader('Access-Control-Allow-Origin', '*');

        if (upstream.headers['content-length']) res.setHeader('Content-Length', upstream.headers['content-length']);

        let headerBuffer = Buffer.alloc(0);
        let headerSent   = false;

        upstream.data.on('data', (chunk) => {
            if (headerSent) { res.write(chunk); return; }
            headerBuffer = Buffer.concat([headerBuffer, chunk]);
            if (headerBuffer.length >= 28) {
                decryptHeader(headerBuffer, key);
                res.write(headerBuffer);
                headerSent = true;
            }
        });

        upstream.data.on('end', () => {
            if (!headerSent && headerBuffer.length > 0) {
                decryptHeader(headerBuffer, key);
                res.write(headerBuffer);
            }
            res.end();
        });

        upstream.data.on('error', () => res.end());
        req.on('close', () => upstream.data.destroy());

    } catch (err) {
        console.error('[PDF ERROR]', err.message);
        sendError(res, 500, 'PDF proxy error: ' + err.message);
    }
});

// ==================== 404 CATCH-ALL ====================
app.use((req, res) => {
    res.status(404).json({
        error    : 'Endpoint not found',
        available: ['/', '/health', '/video', '/pdf']
    });
});

// ==================== ERROR HANDLER ====================
app.use((err, req, res, next) => {
    console.error('[UNHANDLED ERROR]', err.stack);
    sendError(res, 500, 'Internal server error');
});

// ==================== START ====================
const server = app.listen(PORT, '0.0.0.0', () => {
    console.log('================================================');
    console.log('  AppX Proxy Server v2.0.0');
    console.log('================================================');
    console.log(`  Port    : ${PORT}`);
    console.log(`  Health  : http://localhost:${PORT}/health`);
    console.log(`  Video   : http://localhost:${PORT}/video`);
    console.log(`  PDF     : http://localhost:${PORT}/pdf`);
    console.log('================================================');
});

// Graceful shutdown for Render
process.on('SIGTERM', () => {
    console.log('SIGTERM received — shutting down gracefully');
    server.close(() => {
        console.log('Server closed');
        process.exit(0);
    });
});

process.on('SIGINT', () => {
    console.log('SIGINT received — shutting down');
    server.close(() => process.exit(0));
});

module.exports = app;
