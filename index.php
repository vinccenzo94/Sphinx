<?php
require 'api/sphinxapi.php';

define('LIMITE', 10);

// Le quatrième paramètre sert à dire à MySQL que l'on va communiquer des données encodées en UTF-8
$db = new PDO('mysql:host=localhost;dbname=test', 'root', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sphinx = new SphinxClient;

$sphinx->SetServer('localhost', 9312);
$sphinx->SetConnectTimeout(2);

// On ne veut que des news de certaines catégories
$sphinx->SetFilter('categorie', array(1, 4));

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$rang = ($page - 1) * LIMITE;

$sphinx->SetLimits($rang, LIMITE);

$resultat = $sphinx->Query('web', 'news');

$nombrePages = ceil($resultat['total_found'] / LIMITE);

$ids = array_keys($resultat['matches']);
$query = $db->query('SELECT news.titre, categories.nom AS cat_nom FROM news LEFT JOIN categories ON news.categorie = categories.id WHERE news.id IN('.implode(',', $ids).')');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <title>Premier essai de l'API PHP de Sphinx</title>
  </head>
  <body>
    <p><?php echo $resultat['total_found']; ?> résultats ont été trouvés en <?php echo $resultat['time']; ?>s.</p>
    <p>Pages :
      <?php
      // On affiche les liens menant aux différentes pages
      for ($i = 1; $i <= $nombrePages; $i++)
      {
        echo '<a href="?page=', $i, '">', $i, '</a> ';
      }
      ?>
    </p>
    <table style="width:100%; text-align:center; margin: auto;">
      <tr><th>Titre</th><th>Catégorie</th></tr>
      <?php
      while ($news = $query->fetch(PDO::FETCH_ASSOC))
      {
        echo '<tr><td>', $news['titre'], '</td><td>', $news['cat_nom'], '</td></tr>', "\n";
      }
      ?>
    </table>
  </body>
</html>
