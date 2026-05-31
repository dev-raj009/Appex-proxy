# AppX Proxy — Complete Deployment Guide
## Render.com (Node.js) + InfinityFree (PHP Player)

---

## ARCHITECTURE

```
Browser
  │
  ▼
InfinityFree (PHP)
  index.php  ← Player UI
  proxy.php  ← CURL Proxy
  │
  │ (forwards request)
  ▼
Render.com (Node.js)
  server.js  ← XOR Decrypt + Stream
  │
  │ (fetches + decrypts)
  ▼
AppX CDN Server
  (Encrypted .mp4 / .pdf)
```

---

## PART 1 — RENDER.COM DEPLOYMENT

### Step 1: GitHub Repo banao

1. GitHub pe new repository banao (e.g., `appx-proxy`)
2. Ye files upload karo root me:
   - `server.js`
   - `package.json`
   - `render.yaml`
   - `.gitignore`

### Step 2: Render pe Deploy karo

1. https://render.com pe jaao → Sign up / Login
2. Dashboard → **New** → **Web Service**
3. GitHub repo connect karo
4. Settings:
   - **Name**: `appx-proxy-server` (ya kuch bhi)
   - **Environment**: `Node`
   - **Build Command**: `npm install`
   - **Start Command**: `npm start`
   - **Plan**: Free
5. **Create Web Service** click karo
6. Build complete hone ka wait karo (~2-3 min)
7. URL copy karo: `https://appx-proxy-server.onrender.com`

### Step 3: Health Check karo

Browser me open karo:
```
https://YOUR-APP.onrender.com/health
```

Response aise dikhega:
```json
{
  "status": "ok",
  "service": "AppX Proxy Server",
  "uptime": 42
}
```

---

## PART 2 — INFINITYFREE PHP PLAYER

### Step 1: config.php Update karo

`php-player/config.php` kholke Render URL daalo:

```php
define('PROXY_SERVER', 'https://YOUR-APP.onrender.com');
```

### Step 2: InfinityFree Upload

1. https://infinityfree.com → Sign up / Login
2. Control Panel → **File Manager**
3. `htdocs` folder kholke andar jaao
4. Ye 4 files upload karo:
   - `index.php`
   - `proxy.php`
   - `config.php`
   - `.htaccess`
5. Done!

### Step 3: Test karo

Browser me open karo:
```
https://yourdomain.epizy.com/
```

---

## USAGE

### Video Play karna:
1. AppX encrypted URL paste karo URL field me
2. Decryption key daalo (default: `appx-pdf-keyset`)
3. **LOAD →** click karo
4. Video automatically play hoga

### PDF dekhna:
1. **PDF** tab click karo
2. PDF URL paste karo
3. **LOAD →** click karo → new tab me open hoga

### URL se directly load:
```
https://yourdomain.epizy.com/?url=ENCRYPTED_URL&key=appx-pdf-keyset
```

### Keyboard Shortcuts:
- `Space` = Play / Pause
- `→` = +10 seconds forward
- `←` = -10 seconds backward

---

## FILES

| File | Location | Purpose |
|------|----------|---------|
| `server.js` | Render (root) | Node.js proxy + XOR decrypt |
| `package.json` | Render (root) | NPM dependencies |
| `render.yaml` | Render (root) | Auto-deploy config |
| `index.php` | InfinityFree/htdocs | Player UI |
| `proxy.php` | InfinityFree/htdocs | PHP CURL proxy |
| `config.php` | InfinityFree/htdocs | Settings |
| `.htaccess` | InfinityFree/htdocs | Apache config |

---

## TROUBLESHOOTING

### "SERVER ERROR" / Node unreachable:
- Render free tier 15 min baad **sleep** ho jaata hai
- Pehli request pe 30-60 sec lag sakta hai (cold start)
- Ek baar open karo: `https://YOUR-APP.onrender.com/health`
- Phir player use karo

### "PHP PROXY ERROR":
- `config.php` me sahi Render URL hai kya?
- InfinityFree me sab 4 files upload hain kya?

### Video error NETWORK:
- AppX URL expire ho gaya. Naya URL lo.

### Video error DECODE:
- Wrong key. Sahi decryption key daalo.

### 403 Forbidden from Node:
- AppX URL galat ya expired hai.

### 403 from PHP:
- Domain whitelist me add karo `config.php` → `ALLOWED_DOMAINS`

---

## RENDER FREE TIER NOTES

- **Sleep**: 15 min inactivity ke baad service sleep ho jaati hai
- **Cold start**: 30-60 seconds lag sakta hai pehli request pe
- **Bandwidth**: Free me 750 hours/month available hai
- **Solution**: UptimeRobot (free) se `/health` endpoint ko har 10 min ping karo — server jaag raha rahega

### UptimeRobot Setup (Optional but Recommended):
1. https://uptimerobot.com → Free signup
2. New Monitor → HTTP(s)
3. URL: `https://YOUR-APP.onrender.com/health`
4. Interval: 10 minutes
5. Done — server hamesha awake rahega!

---

Made for SpidyUniverse Defence Prep Platform
