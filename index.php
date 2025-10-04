<?php
require 'vendor/autoload.php';

use Phpml\Classification\KNearestNeighbors;
use Phpml\Metric\Accuracy;

$apiKey = '3ae3989ac2a94feab144a4954d7f5d2b'; // remplace par ta clé API
$competition = 'FL1'; // Ligue 1
$status = 'FINISHED';

// --- 1. Récupérer les matchs via l'API ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.football-data.org/v4/competitions/$competition/matches?status=$status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-Auth-Token: $apiKey"]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$matches = $data['matches'];

// --- 2. Calculer forme des équipes sur les 5 derniers matchs ---
$teamHistory = [];
foreach ($matches as $match) {
    foreach (['homeTeam','awayTeam'] as $type) {
        $team = $match[$type]['name'];
        $teamHistory[$team][] = $match;
    }
}

function calculerForme($team, $teamHistory){
    $dernieres = array_slice(array_reverse($teamHistory[$team]),0,5);
    $score = 0;
    foreach ($dernieres as $m){
        $home = $m['homeTeam']['name'];
        $away = $m['awayTeam']['name'];
        $hg = $m['score']['fullTime']['home'];
        $ag = $m['score']['fullTime']['away'];
        if($team==$home){
            if($hg>$ag) $score+=1;
            elseif($hg==$ag) $score+=0.5;
        } else {
            if($ag>$hg) $score+=1;
            elseif($ag==$hg) $score+=0.5;
        }
    }
    return $score/5;
}

// --- 3. Préparer données pour ML ---
$samples = $labels = [];
$matches_data = [];
$team_forms = []; // Ajout pour stocker la forme de chaque équipe

foreach ($matches as $m){
    $home_team = $m['homeTeam']['name'];
    $away_team = $m['awayTeam']['name'];
    $hg = $m['score']['fullTime']['home'];
    $ag = $m['score']['fullTime']['away'];

    $home_form = calculerForme($home_team,$teamHistory);
    $away_form = calculerForme($away_team,$teamHistory);

    // Stocker la forme pour une récupération facile plus tard
    $team_forms[$home_team] = $home_form;
    $team_forms[$away_team] = $away_form;

    $samples[] = [$home_form, $away_form];

    if ($hg>$ag) $labels[]='Domicile (1)';
    elseif ($hg<$ag) $labels[]='Exterieur (2)';
    else $labels[]='Nul (N)';

    $matches_data[] = [
        'home_team'=>$home_team,
        'away_team'=>$away_team,
        'home_form'=>$home_form,
        'away_form'=>$away_form,
        'home_odds'=>2.0, // par défaut
        'draw_odds'=>3.0,
        'away_odds'=>3.5
    ];
}

// --- 4. Entraîner modèle ---
$classifier = new KNearestNeighbors();
$classifier->train($samples,$labels);

// --- 5. Évaluer précision ---
$predicted = $classifier->predict($samples);
$accuracy = \Phpml\Metric\Accuracy::score($labels,$predicted);

// --- 6. Formulaire ---
$teams = array_unique(array_merge(array_column($matches_data,'home_team'), array_column($matches_data,'away_team')));
sort($teams);

$home_team = $_POST['home_team'] ?? null;
$away_team = $_POST['away_team'] ?? null;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Prédiction Paris Ligue 1</title>
    <style>
        /* Global */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #f8f8f8, #e0e0e0);
            margin: 0;
            padding: 0;
            color: #1c1c1e;
        }

        /* Container principal */
        .container {
            max-width: 650px;
            margin: 80px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }

        /* Titres */
        h2 {
            text-align: center;
            font-weight: 600;
            font-size: 2rem;
            color: #111;
            margin-bottom: 30px;
        }

        /* Formulaire : équipe vs équipe */
        form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        /* Sélecteurs */
        select {
            -webkit-appearance: none; /* Supprime le style natif Safari/Chrome */
            -moz-appearance: none;    /* Supprime style Firefox */
            appearance: none;         /* Standard */
            
            padding: 15px 40px 15px 15px; /* Espace pour flèche personnalisée */
            font-size: 1rem;
            border-radius: 12px;
            border: 1px solid #d1d1d6;
            background: #f2f2f7 url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10 10"><polygon points="0,0 10,0 5,5" fill="%23666"/></svg>') no-repeat right 12px center;
            background-size: 12px;
            color: #1c1c1e;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        select:focus {
            outline: none;
            border-color: #007aff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
        }

        /* Ajouter un conteneur pour forcer position relative si nécessaire */
        .select-wrapper {
            position: relative;
            display: inline-block;
            width: 200px;
        }

        /* Petite astuce pour Safari iOS : */
        select::-ms-expand {
            display: none; /* cache la flèche sur IE/Edge */
        }


        /* Texte "VS" entre les équipes */
        .vs {
            font-weight: 700;
            font-size: 1.5rem;
            color: #007aff;
        }

        /* Bouton */
        button {
            padding: 15px 25px;
            font-size: 1rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #007aff, #0a84ff);
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        button:hover {
            background: linear-gradient(135deg, #0a84ff, #007aff);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 122, 255, 0.2);
        }

        /* Résultats */
        .result {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 500;
            margin-top: 20px;
            padding: 15px;
            border-radius: 15px;
            background: #f2f2f7;
            color: #111;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
        }

        /* Précision du modèle */
        p {
            text-align: center;
            font-size: 1rem;
            color: #555;
            margin-bottom: 30px;
        }

    </style>
</head>

<body>
    <div class="container">
        <h2>Prédiction Paris Ligue 1</h2>
        <p>Précision modèle : <?php echo round($accuracy * 100, 2); ?>%</p>

        <form method="post">
            <select name="home_team" required>
                <option value="">Équipe à domicile</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team ?>" <?= ($home_team == $team) ? 'selected' : '' ?>><?= $team ?></option>
                <?php endforeach; ?>
            </select>

            <span class="vs">VS</span>

            <select name="away_team" required>
                <option value="">Équipe à l'extérieur</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team ?>" <?= ($away_team == $team) ? 'selected' : '' ?>><?= $team ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Prédire</button>
        </form>

<?php
if($home_team && $away_team){
    // Récupérer la forme de chaque équipe directement
    $home_form = $team_forms[$home_team] ?? 0; // Utilise 0 si l'équipe n'est pas trouvée
    $away_form = $team_forms[$away_team] ?? 0;

    $sample = [$home_form, $away_form];
    $prediction = $classifier->predict([$sample])[0];

    echo "<div class='result'>Résultat prédit : <b>$prediction</b></div>";

    // Logique de value bet (cotes par défaut pour l'instant)
    $odds = ['home_odds'=>2.0, 'draw_odds'=>3.0, 'away_odds'=>3.5];
    $value_bet = 'Non'; // Par défaut

    if($prediction == 'home_win' && $home_form * $odds['home_odds'] > 1) {
        $value_bet = 'Oui';
    } elseif ($prediction == 'draw' && 0.5 * $odds['draw_odds'] > 1) { // Une estimation pour le nul
        $value_bet = 'Oui';
    } elseif ($prediction == 'away_win' && $away_form * $odds['away_odds'] > 1) {
        $value_bet = 'Oui';
    }

    echo "<div class='result'>Value bet détecté ? <b>$value_bet</b></div>";
}
?>
    </div>
</body>
</html>
