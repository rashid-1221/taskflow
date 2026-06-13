<?php
// Génère les icônes PWA (icon-192.png et icon-512.png)
// À ouvrir UNE SEULE FOIS dans le navigateur, puis supprimer.

$dir = __DIR__ . '/icons';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$sizes = [192, 512];
$generated = [];

foreach ($sizes as $size) {
    $img  = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $trans);

    // Fond arrondi bleu sombre (#161b22)
    $bg = imagecolorallocate($img, 22, 27, 34);
    $r  = intval($size / 6);
    imagefilledrectangle($img, $r, 0, $size - $r, $size, $bg);
    imagefilledrectangle($img, 0, $r, $size, $size - $r, $bg);
    imagefilledellipse($img, $r, $r, $r * 2, $r * 2, $bg);
    imagefilledellipse($img, $size - $r, $r, $r * 2, $r * 2, $bg);
    imagefilledellipse($img, $r, $size - $r, $r * 2, $r * 2, $bg);
    imagefilledellipse($img, $size - $r, $size - $r, $r * 2, $r * 2, $bg);

    // Quatre quadrants (matrice Eisenhower)
    $pad  = intval($size / 8);
    $mid  = intval($size / 2);
    $gap  = intval($size / 22);
    $cr   = intval($size / 18);

    $colors = [
        imagecolorallocate($img, 248, 81,  73),  // Q1 rouge
        imagecolorallocate($img, 63,  185, 80),  // Q2 vert
        imagecolorallocate($img, 210, 153, 34),  // Q3 orange
        imagecolorallocate($img, 139, 148, 158), // Q4 gris
    ];

    $quads = [
        [$pad,      $pad,      $mid - $gap, $mid - $gap],
        [$mid + $gap, $pad,    $size - $pad, $mid - $gap],
        [$pad,      $mid + $gap, $mid - $gap, $size - $pad],
        [$mid + $gap, $mid + $gap, $size - $pad, $size - $pad],
    ];

    foreach ($quads as $i => [$x1, $y1, $x2, $y2]) {
        $c = $colors[$i];
        // Rectangle avec coins arrondis simulés
        imagefilledrectangle($img, $x1 + $cr, $y1, $x2 - $cr, $y2, $c);
        imagefilledrectangle($img, $x1, $y1 + $cr, $x2, $y2 - $cr, $c);
        imagefilledellipse($img, $x1 + $cr, $y1 + $cr, $cr * 2, $cr * 2, $c);
        imagefilledellipse($img, $x2 - $cr, $y1 + $cr, $cr * 2, $cr * 2, $c);
        imagefilledellipse($img, $x1 + $cr, $y2 - $cr, $cr * 2, $cr * 2, $c);
        imagefilledellipse($img, $x2 - $cr, $y2 - $cr, $cr * 2, $cr * 2, $c);
    }

    $path = "$dir/icon-{$size}.png";
    imagepng($img, $path);
    imagedestroy($img);
    $generated[] = "icon-{$size}.png ✅";
}

echo "<h2>Icônes générées</h2><ul>";
foreach ($generated as $f) echo "<li>$f</li>";
echo "</ul><p>✅ Vous pouvez supprimer ce fichier maintenant.</p>";
echo "<p><a href='index.html'>← Retour à TaskFlow</a></p>";
?>
