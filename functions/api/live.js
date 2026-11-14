// Cloudflare Pages Function: GET /api/live
// Επιστρέφει τα σημερινά σκορ των πρωταθλημάτων μας ως {ts, m:{id:[status, home, away, minute]}}.
// Το API key μένει κρυφό στο Cloudflare (Settings → Variables and Secrets → FOOTBALL_API_KEY).
// Cache 60" ώστε όσοι επισκέπτες κι αν μπουν, να γίνεται το πολύ 1 κλήση/λεπτό στο football-data.org.
const COMPS = 'PL,PD,SA,BL1,FL1,DED,PPL,ELC,CL';
const TTL = 60;
let mem = null; // cache μέσα στο ίδιο isolate

const out = (body, status = 200, maxAge = TTL) => new Response(JSON.stringify(body), {
  status,
  headers: { 'content-type': 'application/json; charset=utf-8', 'cache-control': `public, max-age=${maxAge}`, 'access-control-allow-origin': '*' },
});

export async function onRequestGet({ env, request, waitUntil }) {
  const now = Date.now();
  if (mem && now - mem.t < TTL * 1000) return out(mem.body);

  const cache = caches.default;
  const key = new Request(new URL('/api/live?__c=1', request.url).toString());
  const hit = await cache.match(key);
  if (hit) return hit;

  if (!env.FOOTBALL_API_KEY) return out({ error: 'missing FOOTBALL_API_KEY' }, 503, 30);

  const d = (o) => new Date(now + o * 86400000).toISOString().slice(0, 10);
  const url = `https://api.football-data.org/v4/matches?competitions=${COMPS}&dateFrom=${d(-1)}&dateTo=${d(1)}`;
  let data;
  try {
    const r = await fetch(url, { headers: { 'X-Auth-Token': env.FOOTBALL_API_KEY } });
    if (!r.ok) throw new Error('upstream ' + r.status);
    data = await r.json();
  } catch (e) {
    if (mem) return out({ ...mem.body, stale: true }, 200, 30); // παλιά δεδομένα αντί για σφάλμα
    return out({ error: String(e.message || e) }, 502, 20);
  }

  const m = {};
  for (const x of data.matches || []) {
    const ft = (x.score && x.score.fullTime) || {};
    m[x.id] = [x.status, ft.home ?? null, ft.away ?? null, x.minute ?? null];
  }
  const body = { ts: Math.floor(now / 1000), m };
  mem = { t: now, body };
  const res = out(body);
  waitUntil(cache.put(key, res.clone()));
  return res;
}
