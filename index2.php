<?php
session_start();

function generateRandomLabyrinth($rows, $cols) {
    // Initialize the labyrinth with walls
    $labyrinthe = array_fill(0, $rows, array_fill(0, $cols, 1));
    
    // Define starting point at [0][0] for Steeve
    $labyrinthe[0][0] = 0;
    
    $stack = [[0, 0]];
    
    // Directions array: right, down, left, up
    $directions = [[0, 1], [1, 0], [0, -1], [-1, 0]];
    
    while (!empty($stack)) {
        $current = $stack[count($stack) - 1];
        $neighbors = [];
        
        // Get neighbors
        foreach ($directions as $dir) {
            $nx = $current[0] + $dir[0] * 2;
            $ny = $current[1] + $dir[1] * 2;
            if ($nx >= 0 && $nx < $rows && $ny >= 0 && $ny < $cols && $labyrinthe[$nx][$ny] == 1) {
                $neighbors[] = [$nx, $ny, $dir];
            }
        }
        
        if (empty($neighbors)) {
            array_pop($stack);
        } else {
            $neighbor = $neighbors[rand(0, count($neighbors) - 1)];
            $nx = $neighbor[0];
            $ny = $neighbor[1];
            $dir = $neighbor[2];
            
            // Remove walls between current and neighbor
            $labyrinthe[$current[0] + $dir[0]][$current[1] + $dir[1]] = 0;
            $labyrinthe[$nx][$ny] = 0;
            $stack[] = [$nx, $ny];
        }
    }
    
    // Place the exit in a random valid position different from [0][0]
    $exitPos = [rand(0, $rows - 1), rand(0, $cols - 1)];
    while ($labyrinthe[$exitPos[0]][$exitPos[1]] == 1 || ($exitPos[0] == 0 && $exitPos[1] == 0)) {
        $exitPos = [rand(0, $rows - 1), rand(0, $cols - 1)];
    }
    $labyrinthe[$exitPos[0]][$exitPos[1]] = 3;
    
    // Set Steeve's starting position at [0][0]
    $labyrinthe[0][0] = 2;
    
    return $labyrinthe;
}

function initializeGame() {
    $rows = 6;
    $cols = 12;
    $_SESSION['map'] = generateRandomLabyrinth($rows, $cols);
    $_SESSION['pos'] = [0, 0];  // Steeve starts at [0][0]
}

function resetGame() {
    session_destroy();
    session_start();
    initializeGame();
}

function move($direction) {
    $labyrinthe = $_SESSION['map'];
    $pos = $_SESSION['pos'];
    $gameOver = false;

    $labyrinthe[$pos[0]][$pos[1]] = 0;
    $previousPos = $pos;

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

    if ($labyrinthe[$pos[0]][$pos[1]] == 3) {
        $gameOver = true;
    }
    $labyrinthe[$pos[0]][$pos[1]] = 2;

    $_SESSION['previousPos'] = $previousPos;
    $_SESSION['direction'] = $direction;
    $_SESSION['pos'] = $pos;
    $_SESSION['map'] = $labyrinthe;
    return $gameOver;
}

function displayLabyrinthe() {
    $labyrinthe = $_SESSION['map'];
    $pos = $_SESSION['pos'];

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
                $labyrinthe[$i][$j] = 4;
            }
        }
    }
    return $labyrinthe;
}

if (!isset($_SESSION['map']) || !isset($_SESSION['pos'])) {
    initializeGame();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["reset"])) {
        resetGame();
    } elseif (isset($_POST['direction'])) {
        $gameOver = move($_POST['direction']);
    }
}

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
