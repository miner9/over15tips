<?php
// ================== ΡΥΘΜΙΣΕΙΣ ==================

// 1) Δωρεάν API key από https://www.football-data.org/client/register
//    Αν μείνει κενό, το site δείχνει DEMO δεδομένα.
define('API_KEY', getenv('FOOTBALL_API_KEY') ?: '');

// 2) Στοιχεία site
const SITE_NAME   = 'Over15 Tips';
define('SITE_URL', getenv('SITE_URL') ?: 'https://over15tips.pages.dev');
const SITE_EMAIL  = 'info@example.gr';
const SITE_TAGLINE = 'Προγνωστικά Over 1.5 γκολ με βάση τα τελευταία 3 παιχνίδια';

// 3) Πρωταθλήματα (free tier football-data.org). Max 9 για το όριο 10 κλήσεων/λεπτό.
const LEAGUES = [
    'PL'  => 'Premier League',
    'PD'  => 'La Liga',
    'SA'  => 'Serie A',
    'BL1' => 'Bundesliga',
    'FL1' => 'Ligue 1',
    'DED' => 'Eredivisie',
    'PPL' => 'Primeira Liga',
    'ELC' => 'Championship',
];

const DAYS_AHEAD   = 14;     // πόσες μέρες μπροστά (καλύπτει διακοπές εθνικών)
const CACHE_TTL    = 10800;  // ανανέωση δεδομένων κάθε 3 ώρες
const MIN_SHOW     = 60;     // κάτω από αυτό το % δεν εμφανίζεται καν
const TIMEZONE     = 'Europe/Athens';

// ================== ΕΣΟΔΑ ==================

// Google AdSense (π.χ. 'ca-pub-1234567890123456'). Κενό = δείχνει placeholder.
const ADSENSE_CLIENT = '';
const ADSENSE_SLOT_TOP    = '';
const ADSENSE_SLOT_INFEED = '';
const ADSENSE_SLOT_BOTTOM = '';
const ADS_EVERY_N_CARDS   = 5;   // διαφήμιση ανάμεσα στους αγώνες

// Affiliate στοιχηματικής (link από το affiliate πρόγραμμα, ΜΟΝΟ αδειοδοτημένη εταιρεία ΕΕΕΠ)
const AFF_NAME  = '';          // π.χ. 'Bookmaker X'
const AFF_URL   = '';          // tracking link
const AFF_OFFER = 'Δες τις αποδόσεις Over 1.5';
const AFF_BANNER_TEXT = 'Μπόνους εγγραφής για νέους παίκτες — ισχύουν όροι & προϋποθέσεις';

// Telegram κανάλι (κρατάει τους επισκέπτες να γυρνάνε κάθε μέρα)
define('TELEGRAM_URL', getenv('TELEGRAM_URL') ?: ''); // π.χ. 'https://t.me/over15tips'

// Google Analytics 4 (π.χ. 'G-XXXXXXX')
const GA_ID = '';

// ================== TELEGRAM BOT ==================
// 1) Στο Telegram μίλα στον @BotFather → /newbot → πάρε το token
// 2) Φτιάξε κανάλι, πρόσθεσε το bot ως Admin
define('TG_BOT_TOKEN', getenv('TG_BOT_TOKEN') ?: '');   // GitHub secret
define('TG_CHAT_ID', getenv('TG_CHAT_ID') ?: '');       // π.χ. '@over15tips'
const TG_MIN_CONF  = 75;            // μόνο ισχυρές επιλογές στο κανάλι
const TG_MAX_TIPS  = 6;
