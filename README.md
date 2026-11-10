# Over15 Tips
Προγνωστικά Over 1.5 γκολ με βάση τα τελευταία 3 παιχνίδια κάθε ομάδας.
- `php build.php` → στατικό site στο `dist/`
- `php telegram.php tips|results` → Telegram
- GitHub Action: `.github/workflows/update.yml` (κάθε 3 ώρες) → branch `site` → Cloudflare Pages
Secrets: `FOOTBALL_API_KEY`, `TG_BOT_TOKEN` · Variables: `TG_CHAT_ID`, `TELEGRAM_URL`, `SITE_URL`
