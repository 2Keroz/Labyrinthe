<?php
session_start();

function initializeGame()
{
    $labyrinthe = [
        [
            [2,0,0,0,1,1,1,1,1,0,0,0],
            [0,1,0,0,1,0,0,0,0,0,1,0],
            [0,1,0,0,1,0,1,0,1,0,1,0],
            [0,1,1,1,1,0,1,0,1,0,1,0],
            [0,0,0,0,0,0,1,0,1,0,1,0],
            [1,1,1,1,1,1,3,0,1,0,1,0],
        ],
        [
            [2,0,0,0,0,0,0,0,0,0,0,0],
            [0,1,1,1,0,1,1,1,1,1,1,1],
            [0,1,0,1,0,0,0,0,0,0,0,1],
            [0,1,0,0,0,1,1,1,1,1,0,1],
            [0,1,1,1,0,1,1,1,1,1,0,1],
            [0,0,0,0,0,0,0,0,0,1,3,1],
        ],
    ];
    $_SESSION['map'] = $labyrinthe[rand(0, count($labyrinthe) - 1)];
    $_SESSION['pos'] = [0, 0];
}

function resetGame()
{
    session_destroy();
    session_start();
    initializeGame();
}

function move($direction)
{
    $labyrinthe = $_SESSION['map'];
    $pos = $_SESSION['pos'];
    $gameOver = false;

    // Enlever le chat de l'ancienne position
    $labyrinthe[$pos[0]][$pos[1]] = 0;
    $previousPos = $pos; // Garder trace de la position précédente

    // changer la direction a l'aide des fleches
    switch ($direction) {
        case 'up':
            if ($pos[0] - 1 >= 0 && $labyrinthe[$pos[0] - 1][$pos[1]] != 1) {
                $pos[0]--;
            }
            break;
        case 'down':
            if ($pos[0] + 1 < count($labyrinthe) && $labyrinthe[$pos[0] + 1][$pos[1]] != 1) {
                $pos[0]++;
            }
            break;
        case 'left':
            if ($pos[1] - 1 >= 0 && $labyrinthe[$pos[0]][$pos[1] - 1] != 1) {
                $pos[1]--;
            }
            break;
        case 'right':
            if ($pos[1] + 1 < count($labyrinthe[$pos[0]]) && $labyrinthe[$pos[0]][$pos[1] + 1] != 1) {
                $pos[1]++;
            }
            break;
    }

    // Mettre à jour la nouvelle position du chat
    if ($labyrinthe[$pos[0]][$pos[1]] == 3) {
        $gameOver = true;
    }
    $labyrinthe[$pos[0]][$pos[1]] = 2;

    $_SESSION['previousPos'] = $previousPos; // Enregistrer la position précédente
    $_SESSION['direction'] = $direction; // Enregistrer la direction
    $_SESSION['pos'] = $pos;
    $_SESSION['map'] = $labyrinthe;
    return $gameOver;
}

function displayLabyrinthe()
{
    $labyrinthe = $_SESSION['map'];
    $pos = $_SESSION['pos'];
// Verification de cellules adjacentes
    foreach ($labyrinthe as $i => $line) {
        foreach ($line as $j => $cell) {
            if (!(
                ($i === $pos[0] && $j === $pos[1]) ||
                ($i === $pos[0] + 1 && $j === $pos[1]) ||
                ($i === $pos[0] - 1 && $j === $pos[1]) ||
                ($i === $pos[0] && $j === $pos[1] + 1) ||
                ($i === $pos[0] && $j === $pos[1] - 1) ||
                ($i === $pos[0] + 1 && $j === $pos[1] + 1) ||
                ($i === $pos[0] + 1 && $j === $pos[1] - 1) ||
                ($i === $pos[0] - 1 && $j === $pos[1] + 1) ||
                ($i === $pos[0] - 1 && $j === $pos[1] - 1)
            )) {
                // si la cellule n'est pas adjacente au joueur, valeur modifié
                $labyrinthe[$i][$j] = 4;
            }
        }
    }
    // MAJ de la carte
    return $labyrinthe;
}
// Vérifie si les var map et pos sont définies, si c'est pas le cas == jeu pas encore initialisé
if (!isset($_SESSION['map']) || !isset($_SESSION['pos'])) {
    //donc initialisation ici
    initializeGame();
}
// verification du type de requete POST ou GET / post = données par formulaire (input)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // vverrifie sur le formulaire contient un champ reset, si c'est lee cas user a demandé reset du jeu
    if (isset($_POST["reset"])) {
        //reset du jeu
        resetGame();
        // Vérifie si le formulaire soumis contient un champ direction
    } elseif (isset($_POST['direction'])) {
        // déplacement du joueur
        $gameOver = move($_POST['direction']);
    }
}
// maj labyrinthe
$labyrinthe = displayLabyrinthe();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/style.css">
    <title>Le Labyrinthe</title>
</head>
<body>
    <header>
        <h1>Le Labyrinthe</h1>
        <p>Retrouve le creeper dans le labyrinthe</p>
    </header>
    <main>
        <div class="container-game">
            <?php if (isset($gameOver) && $gameOver === true) : 
                session_destroy();
                ?>
                <p id="win">Bien joué ! Clique sur le bouton Rejouer pour rejouer</p>
                <form method="post">
                    <input id="rejouer" type="submit" name="replay" value="Rejouer">
                </form>
            <?php else : ?>
                <table>
                    <?php
                    // Mets les images en place en fonction de leur numéro dans le tableau
                    foreach ($labyrinthe as $row) {
                        echo '<tr>';
                        foreach ($row as $value) {
                            echo '<td>';
                            switch ($value) {
                                case 0:
                                    echo ' ';
                                    break;
                                case 1:
                                    echo '<img src="./assets/img/mur.png">';
                                    break;
                                case 2:
                                    echo '<img src="./assets/img/steeve.png">';
                                    break;
                                case 3:
                                    echo '<img src="./assets/img/creeper.png">';
                                    break;
                                case 4:
                                    echo '<img src="./assets/img/eau.gif">';
                                    break;
                            }
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                    ?>
                </table>
                <form class="bouton" method="post">
                    <input id="haut" type="submit" name="direction" value="up">
                    <input id="bas" type="submit" name="direction" value="down">
                    <input id="gauche" type="submit" name="direction" value="left">
                    <input id="droite" type="submit" name="direction" value="right">
                    <input id="reset" type="submit" name="reset" value="reset">
                </form>
            <?php endif; ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
