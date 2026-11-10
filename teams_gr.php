<?php
// Ελληνικά ονόματα ομάδων (για τίτλους/SEO). Ό,τι λείπει εμφανίζεται με το λατινικό όνομα.
const TEAMS_GR = [
    // Premier League
    'Arsenal' => 'Άρσεναλ', 'Aston Villa' => 'Άστον Βίλα', 'Bournemouth' => 'Μπόρνμουθ', 'Brentford' => 'Μπρέντφορντ',
    'Brighton Hove' => 'Μπράιτον', 'Chelsea' => 'Τσέλσι', 'Coventry City' => 'Κόβεντρι', 'Crystal Palace' => 'Κρίσταλ Πάλας',
    'Everton' => 'Έβερτον', 'Fulham' => 'Φούλαμ', 'Hull City' => 'Χαλ', 'Ipswich Town' => 'Ίπσουιτς', 'Leeds United' => 'Λιντς',
    'Liverpool' => 'Λίβερπουλ', 'Man City' => 'Μάντσεστερ Σίτι', 'Man United' => 'Μάντσεστερ Γιουνάιτεντ', 'Newcastle' => 'Νιούκαστλ',
    'Nottingham' => 'Νότιγχαμ Φόρεστ', 'Sunderland' => 'Σάντερλαντ', 'Tottenham' => 'Τότεναμ', 'West Ham' => 'Γουέστ Χαμ',
    'Wolverhampton' => 'Γουλβς', 'Burnley' => 'Μπέρνλι', 'Leicester City' => 'Λέστερ',
    // Championship
    'Birmingham' => 'Μπέρμιγχαμ', 'Blackburn' => 'Μπλάκμπερν', 'Bolton' => 'Μπόλτον', 'Bristol City' => 'Μπρίστολ Σίτι', 'Cardiff' => 'Κάρντιφ', 'Hull' => 'Χαλ', 'Leicester' => 'Λέστερ', 'Plymouth' => 'Πλίμουθ', 'Luton Town' => 'Λούτον', 'Barnsley' => 'Μπάρνσλεϊ',
    'Charlton' => 'Τσάρλτον', 'Derby County' => 'Ντέρμπι', 'Lincoln City' => 'Λίνκολν', 'Middlesbrough' => 'Μίντλεσμπρο',
    'Millwall' => 'Μίλγουολ', 'Norwich' => 'Νόριτς', 'Portsmouth' => 'Πόρτσμουθ', 'Preston NE' => 'Πρέστον', 'QPR' => 'ΚΠΡ',
    'Sheffield Utd' => 'Σέφιλντ Γιουνάιτεντ', 'Sheffield Wed' => 'Σέφιλντ Γουένσντεϊ', 'Southampton' => 'Σαουθάμπτον',
    'Stoke' => 'Στόουκ', 'Swansea' => 'Σουόνσι', 'Watford' => 'Γουότφορντ', 'West Brom' => 'Γουέστ Μπρομ', 'Wrexham' => 'Ρέξαμ',
    'Oxford United' => 'Όξφορντ',
    // La Liga
    'Alavés' => 'Αλαβές', 'Athletic' => 'Αθλέτικ Μπιλμπάο', 'Atleti' => 'Ατλέτικο Μαδρίτης', 'Barça' => 'Μπαρτσελόνα',
    'Celta' => 'Θέλτα', 'Deportivo' => 'Ντεπορτίβο', 'Elche' => 'Έλτσε', 'Espanyol' => 'Εσπανιόλ', 'Getafe' => 'Χετάφε',
    'Girona' => 'Τζιρόνα', 'Levante' => 'Λεβάντε', 'Málaga' => 'Μάλαγα', 'Mallorca' => 'Μαγιόρκα', 'Osasuna' => 'Οσασούνα',
    'Rayo Vallecano' => 'Ράγιο Βαγιεκάνο', 'Real Betis' => 'Μπέτις', 'Real Madrid' => 'Ρεάλ Μαδρίτης', 'Real Oviedo' => 'Οβιέδο',
    'Real Sociedad' => 'Σοσιεδάδ', 'Santander' => 'Ράσινγκ Σανταντέρ', 'Sevilla FC' => 'Σεβίλλη', 'Valencia' => 'Βαλένθια',
    'Villarreal' => 'Βιγιαρεάλ',
    // Serie A
    'Atalanta' => 'Αταλάντα', 'Bologna' => 'Μπολόνια', 'Cagliari' => 'Κάλιαρι', 'Como 1907' => 'Κόμο', 'Cremonese' => 'Κρεμονέζε',
    'Fiorentina' => 'Φιορεντίνα', 'Frosinone' => 'Φροζινόνε', 'Genoa' => 'Τζένοα', 'Inter' => 'Ίντερ', 'Juventus' => 'Γιουβέντους',
    'Lazio' => 'Λάτσιο', 'Lecce' => 'Λέτσε', 'Milan' => 'Μίλαν', 'Monza' => 'Μόντσα', 'Napoli' => 'Νάπολι', 'Parma' => 'Πάρμα',
    'Pisa' => 'Πίζα', 'Roma' => 'Ρόμα', 'Sassuolo' => 'Σασουόλο', 'Torino' => 'Τορίνο', 'Udinese' => 'Ουντινέζε',
    'Venezia FC' => 'Βενέτσια', 'Verona' => 'Βερόνα',
    // Bundesliga
    '1. FC Köln' => 'Κολωνία', 'Augsburg' => 'Άουγκσμπουργκ', 'Bayern' => 'Μπάγερν', 'Bremen' => 'Βέρντερ Βρέμης',
    'Dortmund' => 'Ντόρτμουντ', 'Elversberg' => 'Έλβερσμπεργκ', 'Frankfurt' => 'Άιντραχτ Φρανκφούρτης', 'Freiburg' => 'Φράιμπουργκ',
    'HSV' => 'Αμβούργο', 'Heidenheim' => 'Χαϊντενχάιμ', 'Hoffenheim' => 'Χόφενχαϊμ', 'Leverkusen' => 'Λεβερκούζεν',
    "M'gladbach" => 'Γκλάντμπαχ', 'Mainz' => 'Μάιντς', 'RB Leipzig' => 'Λειψία', 'SC Paderborn' => 'Πάντερμπορν',
    'Schalke' => 'Σάλκε', 'St. Pauli' => 'Ζανκτ Πάουλι', 'Stuttgart' => 'Στουτγκάρδη', 'Union Berlin' => 'Ουνιόν Βερολίνου',
    'Wolfsburg' => 'Βόλφσμπουργκ',
    // Ligue 1
    'Angers SCO' => 'Ανζέ', 'Auxerre' => 'Οσέρ', 'Brest' => 'Μπρεστ', 'Le Havre' => 'Χάβρη', 'Le Mans' => 'Λε Μαν',
    'Lille' => 'Λιλ', 'Lorient' => 'Λοριάν', 'Marseille' => 'Μαρσέιγ', 'Monaco' => 'Μονακό', 'Nantes' => 'Ναντ', 'Nice' => 'Νις',
    'Olympique Lyon' => 'Λιόν', 'PSG' => 'Παρί Σεν Ζερμέν', 'Paris FC' => 'Παρί FC', 'RC Lens' => 'Λανς',
    'Stade Rennais' => 'Ρεν', 'Strasbourg' => 'Στρασβούργο', 'Toulouse' => 'Τουλούζ', 'Troyes' => 'Τρουά', 'Metz' => 'Μετς',
    // Eredivisie
    'AZ' => 'ΑΖ Άλκμααρ', 'Ajax' => 'Άγιαξ', 'Cambuur' => 'Καμπούρ', 'Den Haag' => 'Χάγη', 'Excelsior' => 'Εξέλσιορ',
    'Feyenoord' => 'Φέγενορντ', 'Go Ahead' => 'Γκόου Αχέντ', 'Groningen' => 'Γκρόνινγκεν', 'Heerenveen' => 'Χέρενφεν',
    'NEC' => 'Ναϊμέγκεν', 'PSV' => 'Αϊντχόφεν', 'Sittard' => 'Φορτούνα Σιτάρντ', 'Sparta' => 'Σπάρτα Ρότερνταμ',
    'Telstar' => 'Τέλσταρ', 'Twente' => 'Τβέντε', 'Utrecht' => 'Ουτρέχτη', 'Willem II' => 'Βίλεμ II', 'Zwolle' => 'Ζβόλε',
    'Heracles' => 'Ηρακλής Αλμέλο', 'NAC Breda' => 'Μπρέντα',
    // Primeira Liga
    'Benfica' => 'Μπενφίκα', 'Porto' => 'Πόρτο', 'FC Porto' => 'Πόρτο', 'Sporting CP' => 'Σπόρτινγκ Λισαβόνας',
    'Braga' => 'Μπράγκα', 'Sporting Braga' => 'Μπράγκα', 'Vitória SC' => 'Γκιμαράες', 'Guimarães' => 'Γκιμαράες',
    'Famalicão' => 'Φαμαλικάο', 'Gil Vicente' => 'Ζιλ Βιθέντε', 'Estoril' => 'Εστορίλ', 'Rio Ave' => 'Ρίο Άβε',
    'Moreirense' => 'Μορεϊρένσε', 'Arouca' => 'Αρούκα', 'Casa Pia' => 'Κάζα Πία', 'Santa Clara' => 'Σάντα Κλάρα',
    'Nacional' => 'Νασιονάλ', 'Estrela' => 'Εστρέλα', 'AVS' => 'AVS', 'Tondela' => 'Τοντέλα', 'Alverca' => 'Αλβέρκα',
];

function gr_team(string $n): string { return TEAMS_GR[$n] ?? $n; }
