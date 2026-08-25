<?php
declare(strict_types=1);

function ud_ai_assistant_config(): array
{
    try {
        $config = require __DIR__ . '/../config/config.php';
        $ai = $config['ai_assistant'] ?? [];
        return [
            'enabled' => (bool)($ai['enabled'] ?? false),
            'provider' => (string)($ai['provider'] ?? 'openai'),
            'api_key' => (string)($ai['api_key'] ?? ''),
            'model' => (string)($ai['model'] ?? 'gpt-4o-mini'),
            'max_input_chars' => max(120, (int)($ai['max_input_chars'] ?? 700)),
            'max_output_tokens' => max(220, (int)($ai['max_output_tokens'] ?? 420)),
            'temperature' => (float)($ai['temperature'] ?? 0.25),
            'timeout_seconds' => max(5, (int)($ai['timeout_seconds'] ?? 18)),
        ];
    } catch (Throwable $e) {
        return [
            'enabled' => false,
            'provider' => 'openai',
            'api_key' => '',
            'model' => 'gpt-4o-mini',
            'max_input_chars' => 700,
            'max_output_tokens' => 420,
            'temperature' => 0.25,
            'timeout_seconds' => 18,
        ];
    }
}

function ud_ai_assistant_detect_intent(string $question): ?string
{
    $q = mb_strtolower(trim($question), 'UTF-8');
    if (
        str_contains($q, 'maison')
        || str_contains($q, 'appartement')
        || str_contains($q, 'acheter')
        || str_contains($q, 'achter')
        || str_contains($q, 'achat')
        || str_contains($q, 'immobilier')
        || str_contains($q, 'logement')
    ) {
        return 'immobilier';
    }
    if (
        str_contains($q, 'entreprise')
        || str_contains($q, 'societe')
        || str_contains($q, 'société')
        || str_contains($q, 'creation')
        || str_contains($q, 'création')
        || str_contains($q, 'business')
    ) {
        return 'entreprise';
    }
    if (
        str_contains($q, 'emploi')
        || str_contains($q, 'travail')
        || str_contains($q, 'cv')
        || str_contains($q, 'formation')
    ) {
        return 'emploi';
    }
    if (
        str_contains($q, 'administratif')
        || str_contains($q, 'document')
        || str_contains($q, 'papier')
        || str_contains($q, 'demarche')
        || str_contains($q, 'démarche')
    ) {
        return 'administratif';
    }
    return null;
}

/**
 * Mots-clés par slug pour aider la détection même si l'utilisateur n'utilise pas le titre exact.
 *
 * @return array<string, list<string>>
 */
function ud_ai_assistant_service_keywords(): array
{
    return [
        'immobilier-btp' => ['immobilier', 'maison', 'appartement', 'achat', 'acheter', 'logement', 'bien', 'terrain', 'btp', 'construction', 'travaux', 'location'],
        'immobilier' => ['immobilier', 'maison', 'appartement', 'achat', 'logement', 'bien'],
        'creation-gestion-d-entreprises' => ['entreprise', 'societe', 'société', 'creer', 'créer', 'creation', 'création', 'gestion', 'business', 'startup', 'auto-entrepreneur', 'autoentrepreneur', 'sasu', 'sarl', 'micro entreprise'],
        'assurances-credits' => ['assurance', 'credit', 'crédit', 'pret', 'prêt', 'financement', 'banque', 'mutuelle'],
        'informatiques' => ['informatique', 'site', 'site web', 'web', 'developpement', 'développement', 'application', 'app', 'logiciel', 'logiciels'],
        'services-a-la-personne' => ['service personne', 'aide a domicile', 'aide à domicile', 'menage', 'ménage', 'garde', 'personne agee', 'personne âgée'],
        'assistances-administratives' => ['administratif', 'administrative', 'papier', 'papiers', 'document', 'documents', 'demarche', 'démarche', 'dossier', 'prefecture', 'préfecture', 'naturalisation', 'titre de sejour', 'titre de séjour', 'visa', 'passeport', 'carte d identite', 'carte d\'identité'],
        'formations-emplois' => ['emploi', 'travail', 'job', 'cv', 'lettre de motivation', 'formation', 'reconversion', 'qualification'],
        'voyages' => ['voyage', 'vol', 'billet', 'sejour', 'séjour', 'vacances', 'destination'],
        'evenementiel' => ['evenement', 'événement', 'mariage', 'fete', 'fête', 'organisation', 'soiree', 'soirée', 'reception'],
        'transferts' => ['transfert', 'envoyer argent', 'envoi argent', 'argent', 'mandat'],
    ];
}

/**
 * Cherche le service le plus pertinent pour une question (titre, slug, mots-clés).
 * Retourne le tableau service complet ou null.
 *
 * @param array<int, array<string,mixed>> $services
 */
function ud_ai_assistant_match_service(string $question, array $services): ?array
{
    $q = mb_strtolower(trim($question), 'UTF-8');
    if ($q === '') {
        return null;
    }
    $kwMap = ud_ai_assistant_service_keywords();

    $best = null;
    $bestScore = 0;

    foreach ($services as $s) {
        if (!is_array($s)) {
            continue;
        }
        $slug = mb_strtolower(trim((string)($s['slug'] ?? '')), 'UTF-8');
        $title = mb_strtolower(trim((string)($s['title'] ?? '')), 'UTF-8');
        $description = mb_strtolower(trim((string)($s['description'] ?? '')), 'UTF-8');
        $score = 0;

        if ($title !== '' && str_contains($q, $title)) {
            $score += 100;
        } else {
            foreach (preg_split('~[\s\-/_]+~u', $title) as $w) {
                $w = trim((string)$w);
                if (mb_strlen($w, 'UTF-8') >= 4 && str_contains($q, $w)) {
                    $score += 20;
                }
            }
        }
        if ($slug !== '') {
            $slugClean = str_replace(['-', '_'], ' ', $slug);
            if (str_contains($q, $slugClean)) {
                $score += 50;
            }
            if (isset($kwMap[$slug])) {
                foreach ($kwMap[$slug] as $kw) {
                    if (str_contains($q, mb_strtolower($kw, 'UTF-8'))) {
                        $score += 35;
                    }
                }
            }
        }
        if ($description !== '') {
            foreach (preg_split('~[\s,.;:]+~u', $description) as $w) {
                $w = trim((string)$w);
                if (mb_strlen($w, 'UTF-8') >= 6 && str_contains($q, $w)) {
                    $score += 5;
                }
            }
        }

        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $s;
        }
    }

    return $bestScore >= 35 ? $best : null;
}

/**
 * Génère une réponse listant les pôles + volets associés.
 * Utilisée pour la slash-command /services et l'aide générale.
 */
function ud_ai_assistant_catalog_answer(string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');
    $services = function_exists('services_all') ? services_all() : [];
    $voletsAll = ud_ai_assistant_volets_all();

    $sections = [
        'Catalogue des accompagnements proposés par Univers Diaspora :',
    ];
    foreach ($services as $s) {
        if (!is_array($s)) {
            continue;
        }
        $title = trim((string)($s['title'] ?? ''));
        $slug = trim((string)($s['slug'] ?? ''));
        if ($title === '' || $slug === '') {
            continue;
        }
        $url = !empty($s['external_url']) ? (string)$s['external_url'] : ($base . '/?page=' . rawurlencode($slug));
        $soon = !empty($s['coming_soon']) ? ' (ouverture prochaine)' : '';
        $voletLabels = [];
        foreach (($voletsAll[$slug] ?? []) as $v) {
            $lab = trim((string)($v['label'] ?? ''));
            if ($lab !== '') {
                $voletLabels[] = $lab;
            }
        }
        $line = '• ' . $title . $soon . ' — ' . $url;
        if (!empty($voletLabels)) {
            $line .= "\n   Volets : " . implode(' / ', array_slice($voletLabels, 0, 5));
        }
        $sections[] = $line;
    }

    $sections[] = 'Indiquez en une phrase votre besoin principal pour une orientation précise.';
    $sections[] = '→ Prendre rendez-vous : ' . ud_appointment_url($base);
    $sections[] = '→ Démarrer un projet structuré : ' . $base . '/?page=demarrer-maintenant';

    return implode("\n", $sections);
}

/**
 * Détecte une commande slash (/services, /aide, /reset, etc.) et retourne
 * la réponse correspondante. Retourne null si ce n'est pas une commande.
 */
function ud_ai_assistant_handle_slash_command(string $question, string $baseUrl): ?string
{
    $q = trim($question);
    if ($q === '' || $q[0] !== '/') {
        return null;
    }
    $cmd = mb_strtolower(preg_replace('/\s.*$/', '', $q) ?? '', 'UTF-8');
    $base = rtrim($baseUrl, '/');
    switch ($cmd) {
        case '/services':
        case '/service':
        case '/catalogue':
        case '/list':
            return ud_ai_assistant_catalog_answer($baseUrl);

        case '/rdv':
        case '/rendez-vous':
        case '/rendezvous':
            return 'Pour prendre rendez-vous dans l’un de nos trois bureaux (Paris 18ᵉ, Paris 17ᵉ, Colombes), utilisez le lien ci-dessous. Vous pourrez choisir la date, l’heure et préciser l’objet de votre venue.' . "\n\n"
                . '→ Prise de rendez-vous : ' . ud_appointment_url($base);

        case '/contact':
        case '/ecrire':
            return 'Pour une demande structurée, le formulaire de contact est le canal recommandé.' . "\n\n"
                . '→ Formulaire de contact : ' . $base . '/#contact' . "\n"
                . '→ Prendre rendez-vous : ' . ud_appointment_url($base);

        case '/aide':
        case '/help':
        case '/?':
            return implode("\n", [
                'Voici comment je peux vous être utile :',
                '• Décrivez votre besoin en une phrase et je vous oriente vers le service adapté.',
                '• Posez une question précise sur l’un des volets (achat, vente, création, démarche, etc.).',
                '',
                'Commandes disponibles :',
                '/services — liste l’ensemble des pôles et volets',
                '/rdv — accès direct à la prise de rendez-vous',
                '/contact — accès au formulaire de contact',
                '/reset — réinitialise la conversation',
            ]);

        default:
            return null;
    }
}

/**
 * Charge le tableau des volets détaillés par service.
 *
 * @return array<string, list<array<string,string>>>
 */
function ud_ai_assistant_volets_all(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $path = __DIR__ . '/../data/service_volets.php';
    if (!is_file($path)) {
        $cache = [];
        return $cache;
    }
    $data = require $path;
    $cache = is_array($data) ? $data : [];
    return $cache;
}

/**
 * Identifie le volet le plus pertinent d'un service à partir de la question.
 * Retourne null si aucun volet n'est suffisamment proche.
 */
function ud_ai_assistant_match_volet(string $question, string $serviceSlug): ?array
{
    $voletsAll = ud_ai_assistant_volets_all();
    if (empty($voletsAll[$serviceSlug])) {
        return null;
    }
    $q = mb_strtolower(trim($question), 'UTF-8');
    if ($q === '') {
        return null;
    }
    $best = null;
    $bestScore = 0;
    foreach ($voletsAll[$serviceSlug] as $v) {
        $label = mb_strtolower((string)($v['label'] ?? ''), 'UTF-8');
        $lead = mb_strtolower((string)($v['lead'] ?? ''), 'UTF-8');
        $score = 0;
        if ($label !== '' && str_contains($q, $label)) {
            $score += 80;
        }
        foreach (preg_split('~[\s\-/_,.;:]+~u', $label) as $w) {
            $w = trim((string)$w);
            if (mb_strlen($w, 'UTF-8') >= 4 && str_contains($q, $w)) {
                $score += 18;
            }
        }
        foreach (preg_split('~[\s\-/_,.;:]+~u', $lead) as $w) {
            $w = trim((string)$w);
            if (mb_strlen($w, 'UTF-8') >= 6 && str_contains($q, $w)) {
                $score += 6;
            }
        }
        if ($score > $bestScore) {
            $bestScore = $score;
            $best = $v;
        }
    }
    return $bestScore >= 30 ? $best : null;
}

/**
 * Mention de cadrage / non-engagement réglementaire par service.
 * Reprend les disclaimers de pages/service.php mais en version courte chat.
 */
function ud_ai_assistant_disclaimer_for_slug(string $slug): string
{
    $map = [
        'assurances-credits' => 'À noter : Univers Diaspora n’est ni banque, ni courtier IOBSP, ni intermédiaire en assurance. Toute souscription relève d’un établissement habilité (immatriculation ORIAS).',
        'creation-gestion-d-entreprises' => 'À noter : nous n’exerçons ni le métier d’avocat, ni d’expert-comptable. Les actes juridiques, comptables et fiscaux engageants relèvent de professionnels habilités.',
        'immobilier-btp' => 'À noter : Univers Diaspora n’est ni notaire, ni agence immobilière (loi Hoguet), ni maître d’œuvre. La signature des actes et l’exécution des travaux relèvent de prestataires habilités.',
        'assistances-administratives' => 'À noter : nous intervenons en assistance administrative et ne sommes pas mandataire d’une administration. Les décisions et actes officiels relèvent des autorités compétentes.',
        'formations-emplois' => 'À noter : nous proposons un accompagnement à l’orientation et à la recherche d’emploi. Les formations certifiantes relèvent d’organismes enregistrés (Qualiopi le cas échéant).',
    ];
    return $map[$slug] ?? '';
}

/**
 * Construit une réponse professionnelle, structurée, ton conseil :
 *   1) Accroche neutre rappelant le service identifié
 *   2) Mission concrète d'Univers Diaspora pour ce service
 *   3) Volets disponibles (3 max), avec mise en avant du volet pertinent
 *   4) Étapes du déroulement (3 max)
 *   5) Mention de cadrage réglementaire si applicable
 *   6) Actions concrètes (page service, RDV pré-rempli, contact)
 */
function ud_ai_assistant_service_answer(array $service, string $baseUrl, string $question = ''): string
{
    $base = rtrim($baseUrl, '/');
    $slug = (string)($service['slug'] ?? '');
    $title = trim((string)($service['title'] ?? ''));
    $description = trim((string)($service['description'] ?? ''));
    $isSoon = !empty($service['coming_soon']);
    $serviceUrl = !empty($service['external_url'])
        ? (string)$service['external_url']
        : ($base . '/?page=' . rawurlencode($slug));

    $matchedVolet = $question !== '' ? ud_ai_assistant_match_volet($question, $slug) : null;

    $voletId = ($matchedVolet !== null && !empty($matchedVolet['id']))
        ? (string)$matchedVolet['id']
        : null;
    $rdvUrl = ud_appointment_url($base, $slug !== '' ? $slug : null, $voletId);

    $sections = [];

    /* 1) Accroche neutre */
    if ($title !== '') {
        if ($matchedVolet !== null) {
            $sections[] = 'Votre demande concerne le service « ' . $title . ' », volet « ' . (string)($matchedVolet['label'] ?? '') . ' ».';
        } else {
            $sections[] = 'Votre demande relève du service « ' . $title . ' ».';
        }
    }

    /* 2) Mission */
    $missionLines = [];
    if ($matchedVolet !== null && !empty($matchedVolet['lead'])) {
        $missionLines[] = (string)$matchedVolet['lead'];
    } elseif ($description !== '') {
        $missionLines[] = $description;
    }
    if ($matchedVolet !== null && !empty($matchedVolet['text'])) {
        $missionLines[] = (string)$matchedVolet['text'];
    }
    if (!empty($missionLines)) {
        $sections[] = implode(' ', $missionLines);
    }

    /* 3) Volets disponibles (top 3) */
    $voletsAll = ud_ai_assistant_volets_all();
    $serviceVolets = $voletsAll[$slug] ?? [];
    if (!empty($serviceVolets)) {
        $voletsToShow = $serviceVolets;
        if ($matchedVolet !== null) {
            usort($voletsToShow, static function (array $a, array $b) use ($matchedVolet): int {
                $aId = (string)($a['id'] ?? '');
                $bId = (string)($b['id'] ?? '');
                $mId = (string)($matchedVolet['id'] ?? '');
                if ($aId === $mId) return -1;
                if ($bId === $mId) return 1;
                return 0;
            });
        }
        $voletsToShow = array_slice($voletsToShow, 0, 3);
        $voletLines = ['Volets d’accompagnement disponibles :'];
        foreach ($voletsToShow as $v) {
            $label = trim((string)($v['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $voletLines[] = '• ' . $label;
        }
        if (count($voletLines) > 1) {
            $sections[] = implode("\n", $voletLines);
        }
    }

    /* 4) Étapes (3 max) */
    $steps = function_exists('service_steps_for_display') ? service_steps_for_display($service) : [];
    if (!empty($steps)) {
        $stepLines = ['Comment cela se déroule :'];
        $n = 0;
        foreach ($steps as $st) {
            $stTitle = trim((string)($st['title'] ?? ''));
            $stText = trim((string)($st['text'] ?? ''));
            if ($stTitle === '' && $stText === '') {
                continue;
            }
            $n++;
            if ($n > 3) break;
            $line = $n . '. ';
            if ($stTitle !== '') {
                $line .= $stTitle;
                if ($stText !== '') {
                    $line .= ' — ' . $stText;
                }
            } else {
                $line .= $stText;
            }
            $stepLines[] = $line;
        }
        if (count($stepLines) > 1) {
            $sections[] = implode("\n", $stepLines);
        }
    }

    /* 5) Mention de cadrage réglementaire */
    $disclaimer = ud_ai_assistant_disclaimer_for_slug($slug);
    if ($disclaimer !== '') {
        $sections[] = $disclaimer;
    }

    /* 6) Actions concrètes */
    if ($isSoon) {
        $sections[] = 'Ce service ouvre prochainement. Vous pouvez nous laisser vos coordonnées via le formulaire de contact pour être informé(e) dès l’ouverture.';
        $sections[] = 'Fiche du service : ' . $serviceUrl;
    } else {
        $actions = [
            'Prochaines étapes :',
            '→ Fiche détaillée du service : ' . $serviceUrl,
            '→ Prendre rendez-vous (Paris 18ᵉ, Paris 17ᵉ ou Colombes) : ' . $rdvUrl,
        ];
        $sections[] = implode("\n", $actions);
    }

    return implode("\n\n", array_filter(array_map('trim', $sections), static fn($s) => $s !== ''));
}

function ud_ai_assistant_session_context_get(): array
{
    $ctx = $_SESSION['ai_assistant_ctx'] ?? [];
    if (!is_array($ctx)) {
        $ctx = [];
    }
    $history = $ctx['history'] ?? [];
    if (!is_array($history)) {
        $history = [];
    }
    return [
        'intent' => is_string($ctx['intent'] ?? null) ? $ctx['intent'] : null,
        'history' => array_values(array_filter($history, static fn($m) => is_array($m) && isset($m['role'], $m['content']))),
    ];
}

function ud_ai_assistant_session_context_save(?string $intent, array $history): void
{
    $_SESSION['ai_assistant_ctx'] = [
        'intent' => $intent,
        'history' => array_slice($history, -8),
    ];
}

function ud_ai_assistant_session_context_clear(): void
{
    unset($_SESSION['ai_assistant_ctx']);
}

function ud_ai_assistant_system_prompt(string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');
    $services = function_exists('services_all') ? services_all() : [];
    $voletsAll = ud_ai_assistant_volets_all();

    $serviceLines = [];
    foreach ($services as $s) {
        if (!is_array($s)) {
            continue;
        }
        $title = trim((string)($s['title'] ?? ''));
        $slug = trim((string)($s['slug'] ?? ''));
        if ($title === '' || $slug === '') {
            continue;
        }
        $url = !empty($s['external_url']) ? (string)$s['external_url'] : ($base . '/?page=' . rawurlencode($slug));
        $desc = trim((string)($s['description'] ?? ''));
        $line = '- ' . $title . ' (' . $url . ')';
        if ($desc !== '') {
            $line .= ' : ' . $desc;
        }
        $steps = function_exists('service_steps_for_display') ? service_steps_for_display($s) : [];
        $stepStrs = [];
        $n = 1;
        foreach ($steps as $st) {
            $stTitle = trim((string)($st['title'] ?? ''));
            $stText = trim((string)($st['text'] ?? ''));
            if ($stTitle !== '' || $stText !== '') {
                $piece = $stTitle;
                if ($stText !== '') {
                    $piece .= $stTitle !== '' ? ' — ' . $stText : $stText;
                }
                $stepStrs[] = $n . ') ' . $piece;
                $n++;
            }
        }
        if (!empty($stepStrs)) {
            $line .= ' [Étapes: ' . implode(' ; ', $stepStrs) . ']';
        }
        if (!empty($voletsAll[$slug])) {
            $voletLabels = [];
            foreach ($voletsAll[$slug] as $v) {
                $lab = trim((string)($v['label'] ?? ''));
                if ($lab !== '') {
                    $voletLabels[] = $lab;
                }
            }
            if (!empty($voletLabels)) {
                $line .= ' [Volets: ' . implode(' / ', $voletLabels) . ']';
            }
        }
        $serviceLines[] = $line;
    }
    if (empty($serviceLines)) {
        $serviceLines[] = '- Services Univers Diaspora (voir la page Services)';
    }

    return implode("\n", [
        'Tu es l’assistant conversationnel d’Univers Diaspora, cabinet d’accompagnement pour la diaspora à Paris (18ᵉ et 17ᵉ) et Colombes.',
        '',
        '## Identité et posture',
        '- Ton : professionnel, posé, neutre, sobre. Vouvoiement systématique. Aucune familiarité, aucun emoji.',
        '- Tu es un assistant d’information et d’orientation, pas un conseiller juridique, fiscal, financier ou médical.',
        '- Tu ne promets jamais de résultat, de délai ni de tarif. Tu n’invoques aucune anecdote, aucun cas client, aucun chiffre non vérifié.',
        '- Tu ne flattes pas l’utilisateur. Phrases interdites : « Très bonne question », « Excellent projet », « Parfait », « Bien sûr ! », « Je comprends totalement », « Absolument ».',
        '- Tu n’utilises pas de superlatifs marketing (« le meilleur », « unique », « incomparable »).',
        '',
        '## Cadre réglementaire (à respecter)',
        '- Crédits, banque, assurance : Univers Diaspora n’est ni IOBSP, ni intermédiaire en assurance ; oriente vers un courtier ou un assureur immatriculé à l’ORIAS.',
        '- Création / gestion d’entreprise : oriente vers avocat ou expert-comptable pour les actes juridiques, comptables et fiscaux.',
        '- Immobilier : oriente vers notaire et professionnels habilités (loi Hoguet, BTP) pour les actes et travaux engageants.',
        '- Administratif : Univers Diaspora n’est pas mandataire d’une administration ; les décisions officielles relèvent des autorités compétentes.',
        '',
        '## Format de réponse imposé',
        'Si la demande concerne clairement un service du catalogue, structure ta réponse ainsi (5 sections, en respectant les sauts de ligne) :',
        '  1) Une phrase qui identifie le service (et le volet le plus probable s’il y en a un).',
        '  2) Une à deux phrases qui expliquent concrètement ce qu’apporte Univers Diaspora.',
        '  3) Une liste de 2 à 3 volets sous forme de puces « • Volet ».',
        '  4) Une mention de cadrage si le sujet est sensible (cf. cadre réglementaire).',
        '  5) Deux liens d’action obligatoires : la fiche du service, puis le lien de prise de rendez-vous (de préférence pré-rempli : /rendez-vous/<slug>/<volet>).',
        'Réponse globale : 6 à 10 lignes. Pas plus. Aucune invention.',
        '',
        'Si la demande est une simple salutation, un remerciement ou une question floue, réponds sobrement en 2 à 3 phrases sans la structure ci-dessus, et invite à reformuler le besoin principal.',
        '',
        '## Liens utiles',
        '- Site : ' . $base . '/',
        '- Tous les services : ' . $base . '/#services',
        '- Prendre rendez-vous : ' . ud_appointment_url($base),
        '- Démarrer maintenant : ' . $base . '/?page=demarrer-maintenant',
        '- Formulaire de contact : ' . $base . '/#contact',
        '',
        '## Catalogue de services (titre, URL, description, étapes, volets)',
        implode("\n", $serviceLines),
    ]);
}

function ud_ai_assistant_fallback_answer(string $question, string $baseUrl, ?string $intent = null): string
{
    $q = mb_strtolower(trim($question), 'UTF-8');
    $base = rtrim($baseUrl, '/');
    $rdvUrl = ud_appointment_url($base);
    $servicesUrl = $base . '/#services';
    $contactUrl = $base . '/#contact';
    $startUrl = $base . '/?page=demarrer-maintenant';

    $isGreeting = in_array($q, ['bonjour', 'bonsoir', 'salut', 'hello', 'bjr', 'bojour', 'coucou'], true)
        || preg_match('/^(bonjour|bonsoir|salut|hello)\b/u', $q) === 1;
    $isThanks = str_contains($q, 'merci');
    $isResolved = preg_match('/c[\'’ ]?est (réglé|regle|bon|ok)/u', $q) === 1
        || str_contains($q, 'tout est regle') || str_contains($q, 'tout est réglé');
    $isGoodbye = in_array($q, ['au revoir', 'aurevoir', 'bonne journee', 'bonne journée', 'bonne soiree', 'bonne soirée', 'a bientot', 'à bientôt'], true);

    if ($isGreeting) {
        return implode("\n", [
            'Bonjour, je suis l’assistant d’Univers Diaspora.',
            'Pour vous orienter efficacement, indiquez en une phrase votre besoin principal (par exemple : achat immobilier, création d’entreprise, démarche administrative, recherche d’emploi, voyage).',
            '',
            '→ Tous les services : ' . $servicesUrl,
            '→ Prendre rendez-vous : ' . $rdvUrl,
        ]);
    }
    if ($isThanks || $isResolved) {
        return implode("\n", [
            'Pris en compte. Si un autre point reste à traiter, n’hésitez pas à me le préciser.',
            '',
            '→ Prendre rendez-vous : ' . $rdvUrl,
            '→ Nous écrire : ' . $contactUrl,
        ]);
    }
    if ($isGoodbye) {
        return 'Bonne continuation. Vous pouvez revenir à tout moment via la page Services ou la prise de rendez-vous : ' . $rdvUrl . '.';
    }

    if (preg_match('/\b(rendez[- ]?vous|rdv|prendre rdv|prise de rdv|appointment)\b/u', $q) === 1) {
        return implode("\n", [
            'La prise de rendez-vous se fait en ligne, dans l’un de nos trois bureaux : Paris 18ᵉ (19 rue Richomme — métro 2, 4, 12), Paris 17ᵉ (75 rue des Moines — métro 13, 14) ou Colombes (21 rue M. Berteaux — Transilien J, métro 13, bus 140/235/276/340/366).',
            'Choisissez votre date, votre créneau et précisez l’objet de votre venue. Notre équipe revient vers vous pour confirmation.',
            '',
            '→ Page de réservation : ' . $rdvUrl,
        ]);
    }

    if (preg_match('/\b(prix|tarif|tarifs|co[ûu]t|honoraires|combien)\b/u', $q) === 1) {
        return implode("\n", [
            'Les conditions tarifaires dépendent du service mobilisé et du périmètre exact de votre dossier. Univers Diaspora ne communique pas de grille générique sans avoir cadré le besoin au préalable.',
            'Le plus efficace est un premier échange (gratuit, sans engagement) lors duquel nous précisons le périmètre et les conditions applicables.',
            '',
            '→ Prendre rendez-vous : ' . $rdvUrl,
            '→ Nous écrire : ' . $contactUrl,
        ]);
    }

    if (preg_match('/\b(d[ée]lai|d[ée]lais|combien de temps|quand|rapidement|urgence|urgent)\b/u', $q) === 1) {
        return implode("\n", [
            'Les délais varient selon la nature du dossier, les pièces déjà disponibles et les acteurs externes (administrations, partenaires). Aucune estimation fiable ne peut être donnée sans cadrage préalable.',
            'Pour avancer concrètement, un rendez-vous permet de poser un calendrier réaliste et les jalons de suivi.',
            '',
            '→ Prendre rendez-vous : ' . $rdvUrl,
        ]);
    }

    if (preg_match('/\b(contact|email|e[- ]?mail|t[ée]l[ée]phone|num[ée]ro|joindre)\b/u', $q) === 1) {
        return implode("\n", [
            'Plusieurs canaux sont disponibles selon votre besoin :',
            '• Pour une demande structurée : formulaire de contact.',
            '• Pour un échange de cadrage : prise de rendez-vous en ligne.',
            '',
            '→ Formulaire de contact : ' . $contactUrl,
            '→ Prendre rendez-vous : ' . $rdvUrl,
        ]);
    }

    if (
        preg_match('/\b(service|services|aide|accompagnement|que faites|que proposez|p[oô]les)\b/u', $q) === 1
        || $intent === null && $q !== ''
    ) {
        return implode("\n", [
            'Univers Diaspora couvre plusieurs pôles : conseils et accompagnement, immobilier et BTP, voyages, création et gestion d’entreprises, transports, assistances administratives, formation et emplois, services à la personne, assurances et crédits, informatique, supermarket, pompes funèbres, et autres services.',
            'Pour vous orienter précisément, indiquez en une phrase votre besoin principal (achat, vente, location, création, financement, démarche, formation, etc.).',
            '',
            '→ Tous les services : ' . $servicesUrl,
            '→ Démarrer un projet structuré : ' . $startUrl,
            '→ Prendre rendez-vous : ' . $rdvUrl,
        ]);
    }

    return implode("\n", [
        'Pour vous orienter avec précision, indiquez en une phrase votre besoin principal (par exemple : achat immobilier, création d’entreprise, démarche administrative, recherche d’emploi).',
        '',
        '→ Tous les services : ' . $servicesUrl,
        '→ Prendre rendez-vous : ' . $rdvUrl,
    ]);
}

function ud_ai_assistant_call_openai(string $question, string $baseUrl, array $cfg, array $history = []): ?string
{
    if (!function_exists('curl_init')) {
        return null;
    }
    $apiKey = trim((string)$cfg['api_key']);
    if ($apiKey === '') {
        return null;
    }

    $messages = [
        ['role' => 'system', 'content' => ud_ai_assistant_system_prompt($baseUrl)],
    ];
    foreach ($history as $h) {
        if (!is_array($h)) {
            continue;
        }
        $role = (string)($h['role'] ?? '');
        $content = trim((string)($h['content'] ?? ''));
        if (!in_array($role, ['user', 'assistant'], true) || $content === '') {
            continue;
        }
        $messages[] = ['role' => $role, 'content' => $content];
    }
    $messages[] = ['role' => 'user', 'content' => trim($question)];

    $payload = [
        'model' => (string)$cfg['model'],
        'messages' => $messages,
        'temperature' => (float)$cfg['temperature'],
        'max_tokens' => (int)$cfg['max_output_tokens'],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_CONNECTTIMEOUT => (int)$cfg['timeout_seconds'],
        CURLOPT_TIMEOUT => (int)$cfg['timeout_seconds'],
    ]);
    $raw = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if (!is_string($raw) || $status < 200 || $status >= 300) {
        return null;
    }
    $data = json_decode($raw, true);
    $content = trim((string)($data['choices'][0]['message']['content'] ?? ''));
    return $content !== '' ? $content : null;
}

function ud_ai_assistant_handle_http(string $baseUrl): void
{
    header('Content-Type: application/json; charset=utf-8');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
        exit;
    }

    $cfg = ud_ai_assistant_config();
    if (empty($cfg['enabled'])) {
        http_response_code(503);
        echo json_encode(['ok' => false, 'error' => 'assistant_disabled']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $json = is_string($raw) ? json_decode($raw, true) : null;
    $question = trim((string)($json['message'] ?? ''));
    if ($question === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'empty_message']);
        exit;
    }

    if (function_exists('mb_substr')) {
        $question = mb_substr($question, 0, (int)$cfg['max_input_chars']);
    } else {
        $question = substr($question, 0, (int)$cfg['max_input_chars']);
    }

    $qNormalized = mb_strtolower(trim($question), 'UTF-8');
    if (in_array($qNormalized, ['reset', '/reset', 'recommencer', 'restart', 'nouvelle conversation'], true)) {
        ud_ai_assistant_session_context_clear();
        $resetAnswer = 'Contexte réinitialisé. Indiquez en une phrase votre besoin principal pour une orientation adaptée.';
        ud_ai_assistant_log_conversation($question, $resetAnswer, null, null, null);
        echo json_encode([
            'ok' => true,
            'answer' => $resetAnswer,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    $ctx = ud_ai_assistant_session_context_get();
    $detectedIntent = ud_ai_assistant_detect_intent($question);
    $intent = $detectedIntent ?? $ctx['intent'];
    $history = $ctx['history'];

    $matchedService = null;
    $matchedVolet = null;

    /* 0) Slash-commands prioritaires */
    $slashAnswer = ud_ai_assistant_handle_slash_command($question, $baseUrl);
    $answer = $slashAnswer;

    if ($answer === null) {
        $services = function_exists('services_all') ? services_all() : [];
        $matchedService = ud_ai_assistant_match_service($question, $services);
        if ($matchedService !== null) {
            $answer = ud_ai_assistant_service_answer($matchedService, $baseUrl, $question);
            $matchedVolet = ud_ai_assistant_match_volet($question, (string)($matchedService['slug'] ?? ''));
        }
        if ($answer === null || trim($answer) === '') {
            if (($cfg['provider'] ?? 'openai') === 'openai') {
                $answer = ud_ai_assistant_call_openai($question, $baseUrl, $cfg, $history);
            }
        }
        if ($answer === null || trim($answer) === '') {
            $answer = ud_ai_assistant_fallback_answer($question, $baseUrl, $intent);
        }
    }

    $history[] = ['role' => 'user', 'content' => $question];
    $history[] = ['role' => 'assistant', 'content' => (string)$answer];
    ud_ai_assistant_session_context_save($intent, $history);

    ud_ai_assistant_log_conversation(
        $question,
        (string)$answer,
        $intent,
        $matchedService !== null ? (string)($matchedService['slug'] ?? '') : null,
        $matchedVolet !== null ? (string)($matchedVolet['id'] ?? '') : null
    );

    echo json_encode([
        'ok' => true,
        'answer' => trim((string)$answer),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Insère une ligne dans `ai_conversations` (table créée à la volée par app/db.php).
 * Échec silencieux : aucune coupure de l'expérience utilisateur.
 */
function ud_ai_assistant_log_conversation(
    string $question,
    string $answer,
    ?string $intent,
    ?string $matchedSlug,
    ?string $matchedVoletId
): void {
    if (!function_exists('db')) {
        return;
    }
    try {
        $pdo = db();
        $sessionId = (string)(session_id() ?: '');
        if ($sessionId === '') {
            $sessionId = substr(bin2hex(random_bytes(8)), 0, 16);
        }
        $sessionId = substr($sessionId, 0, 64);
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
        $stmt = $pdo->prepare(
            'INSERT INTO ai_conversations (session_id, ip, user_agent, question, answer, intent, matched_service_slug, matched_volet_id)
             VALUES (:sid, :ip, :ua, :q, :a, :intent, :slug, :vid)'
        );
        $stmt->execute([
            ':sid' => $sessionId,
            ':ip' => $ip !== '' ? $ip : null,
            ':ua' => $ua !== '' ? $ua : null,
            ':q' => mb_substr($question, 0, 2000),
            ':a' => mb_substr($answer, 0, 5000),
            ':intent' => $intent,
            ':slug' => $matchedSlug,
            ':vid' => $matchedVoletId,
        ]);
    } catch (Throwable $e) {
        // log best-effort uniquement
    }
}

