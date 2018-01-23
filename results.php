<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" /> 
        <title>JDMClient</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <link rel="stylesheet" href="./css/jquery-ui.css">
        <link rel="stylesheet" href="./css/style.css">
    </head>

    <body>

        <div class="header">
            <center><a href="index.php"><img src="./img/logo.jpg" class="logo"/></a></center>
        </div>

        <div id="navbar">

            <a href="index.php">Accueil</a>

            <form autocomplete="off" class="searchForm"  action="results.php">
                <div class="input-group">
                    <input name="terme" id="terme" type="text" class="form-control" placeholder="Entrez un terme" name="q">
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-success" type="button">Rechercher</button>
                    </div>
                </div>
            </form>

        </div>

        <div class="content">


            <?php

            function getResultsFromCache($filename) {
                $now = time();
                $fileLastModifDate = filemtime($filename);
                $datediff = $now - $fileLastModifDate;

                // si le fichier de cache date de plus de 15 jours, on ajoute son nom dans la liste des fichiers à  update.

                if (floor($datediff / (60 * 60 * 24)) >= 15) {
                    $toUpdateFile = fopen("toUpdate.txt", "wr") or die("Unable to open file!");
                    fwrite($toUpdateFile, $filename);
                    fclose($toUpdateFile);
                }
                readfile($filename); //affichage du fichier html dans le navigateur
            }

            function getResultsFromServer($terme) {
                $url = 'http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&';
                $data = array(
                    'gotermrel' => utf8_decode($terme)
                );
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data)
                    )
                );
                $context = stream_context_create($options);
                $result = @file_get_contents($url, false, $context); // recupération des données depuis le serveur.
                if ($result === FALSE) {
                    return false;
                } else {
                    return $result;
                }
            }

            function getTextBetweenStrings($startTag, $endTag, $ServerResults) {
                $startsAt = strpos($ServerResults, $startTag) + strlen($startTag);
                $endsAt = strpos($ServerResults, $endTag, $startsAt);
                $result = substr($ServerResults, $startsAt, $endsAt - $startsAt);
                $result = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $result); // suppression des ligne blanches
                return $result;
            }

            function getAllNodesFromResults($ServerResults) {
                $startTag = "// les noeuds/termes (Entries) : e;eid;'name';type;w;'formated name'";
                $endTag = "// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ";
                $nodes = getTextBetweenStrings($startTag, $endTag, $ServerResults);
                $allNodes = []; // key = nodeId , value = nodeWeight
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $nodes) as $line) { // parcours chaque ligne
                    $node = explode(";", $line);
                    if ($line != '') {
                        $node[2] = substr($node[2], 1, -1);
                        $allNodes[$node[1]] = $node[2];
                    }
                }
                return $allNodes;
            }

            function getAllOutRelationsFromResults($ServerResults) {

                $startTag = "// les relations sortantes : r;rid;node1;node2;type;w";
                $endTag = "// les relations entrantes : r;rid;node1;node2;type;w ";
                $allOutRelations = getTextBetweenStrings($startTag, $endTag, $ServerResults);
                return $allOutRelations;
            }

            function getAllInRelationsFromResults($ServerResults) {

                $startTag = "// les relations entrantes : r;rid;node1;node2;type;w ";
                $endTag = "// END";
                $allInRelations = getTextBetweenStrings($startTag, $endTag, $ServerResults);
                return $allInRelations;
            }

            function getAllRelationTypesFromResults($ServerResults) {
                $startTag = "// les types de relations (Relation Types) : rt;rtid;'trname';'trgpname';'rthelp' ";
                $endTag = "// les relations sortantes : r;rid;node1;node2;type;w ";
                $relationTypes = getTextBetweenStrings($startTag, $endTag, $ServerResults);
                $allRelationTypes = []; // key = rtid , value = rtname
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $relationTypes) as $line) { // parcours chaque ligne
                    $relationType = explode(";", $line);
                    if ($line != '') {
                        $relationType[3] = substr($relationType[3], 1, -1);
                        $allRelationTypes[$relationType[1]] = $relationType[3];
                    }
                }
                return $allRelationTypes;
            }

            function displayDefinitions($ServerResults) {
                $startTag = "<def>";
                $endTag = "</def>";
                $definition = getTextBetweenStrings($startTag, $endTag, $ServerResults);
                print_r($definition);
            }

            function displayNodesNames($allNodes, &$nodesArray, $rtId) {
                echo '<br /> Nombre de termes: ' . count($nodesArray) . '<br /><br />';
                foreach ($nodesArray as $key => $value) {
                    if (array_key_exists($key, $allNodes)) {
                        echo '<a title="id=' . $key . ' poids= ' . $value . '" href="./results.php?terme=' . $allNodes[$key] . '&relationType=' . $rtId . '">' . $allNodes[$key] . '</a>';
                        echo ' | ';
                    }
                }
            }

            function sortNodesByDescWeight($nodes) {
                uasort($nodes, function ($a, $b) { // tri par poids decroissants
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a > $b) ? -1 : 1;
                });
                return $nodes;
            }

            function getNodesIdsFromOutRelations($outRelations, $rtid) {
                $nodes = []; // création du tableau de noeuds
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $outRelations) as $line) { // parcours chaque ligne
                    $relation = explode(";", $line);
                    if (count($relation) == 6) {
                        if ($relation[4] == $rtid) { // si le type correspond
                            $nodes[$relation[3]] = $relation[5]; // ajout id + poid du noeud au tableau de noeuds
                        }
                    }
                }
                $nodes = sortNodesByDescWeight($nodes);
                return $nodes;
            }

            function getNodesIdsFromInRelations($inRelations, $rtid) {
                $nodes = []; // création du tableau de noeuds
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $inRelations) as $line) { // parcours chaque ligne
                    $relation = explode(";", $line);
                    if (count($relation) == 6) {
                        if ($relation[4] == $rtid) { // si le type correspond
                            $nodes[$relation[2]] = $relation[5]; // ajout id + poid du noeud au tableau de noeuds
                        }
                    }
                }
                $nodes = sortNodesByDescWeight($nodes);
                return $nodes;
            }

            if (defined('STDIN')) { // Si le script php est exécuté en ligne de commande
                $terme = $argv[1];
                $rtId = $argv[2];
            } else { // si le script est exécuté depuis un navigateur web.
                $terme = $_GET['terme']; // Recupération du terme
                //$rtId =  $_GET["relationType"]; //Recupération du type de relation choisi dans le formulaire
                $rtId = -1;
            }

            $filename = './CACHE/' . $terme . '.html';

            if (file_exists($filename)) { //si la recherche a déja été effectuée par le passé, on utilise le cache.
                getResultsFromCache($filename);
            } else { // sinon on envoi une requête au serveur pour recuperer les données et les traiter.
                // Start output buffering
                ob_start();

                $ServerResults = getResultsFromServer($terme); // requête au serveur
                if ($ServerResults === FALSE) {
                     echo "<center><h2 style='color : red;'>Echec de connexion au serveur</h2></center>";
                } else {
                    $ServerResults = print_r(utf8_encode($ServerResults), true);

                    if (strpos($ServerResults, '<CODE>') == false) {
                        echo "<center><h2 style='color : red;'>Le terme n'existe pas</h2></center>";
                    } else {

                        $allNodes = getAllNodesFromResults($ServerResults);
                        $allOutRelations = getAllOutRelationsFromResults($ServerResults);
                        if (strpos($ServerResults, "// les relations entrantes : r;rid;node1;node2;type;w") !== FALSE) {
                            $allInRelations = getAllInRelationsFromResults($ServerResults);
                        } else {
                            $allInRelations = null;
                        }
                        $allRelationTypes = getAllRelationTypesFromResults($ServerResults);

                        //echo '<hr>';
                        echo '<div class="panel-group" id="accordion">';
                        echo '<div class="panel panel-default">
                                <div class="panel-heading">
                                  <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapsedef">
                                   <center><b>Définitions</b></center></a>
                                  </h4>
                                </div>
                                <div id="collapsedef" class="panel-collapse collapse in">
                                  <div class="panel-body">';
                        displayDefinitions($ServerResults);
                        echo '</div>
                                </div>
                              </div>';

                        if ($rtId == "-1") { // -1 = tous les types de relations
                            foreach ($allRelationTypes as $id => $rtName) { // Pour chaque types de relation
                                //echo '<hr>';
                                echo '<div class="panel panel-default">
                                        <div class="panel-heading">
                                          <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse' . $id . '">
                                           <center><b>' . $rtName . '</b></center></a>
                                          </h4>
                                        </div>
                                        <div id="collapse' . $id . '" class="panel-collapse collapse">
                                          <div class="panel-body">';
                                // echo '<div id="rtype"><center><u><h3>' . $rtName . ' : </h3></u></center></div>';
                                echo '<u><h4> Relations sortantes : </h4></u>';
                                $nodesFromOutRelations = getNodesIdsFromOutRelations($allOutRelations, $id);
                                displayNodesNames($allNodes, $nodesFromOutRelations, $rtId);
                                if ($allInRelations != null) {
                                    echo '<br /><br />';
                                    echo '<u><h4> Relations entrantes :</h4></u>';
                                    $nodesFromInRelations = getNodesIdsFromInRelations($allInRelations, $id);
                                    displayNodesNames($allNodes, $nodesFromInRelations, $rtId);
                                }
                                echo '</div>
                                    </div>
                                  </div>';
                            }
                            echo '</div>';
                        } else {
                            $rtName = $allRelationTypes[$rtId];
                            //echo '<hr>';
                            echo '<div class="panel-group" id="accordion">
                            <div class="panel panel-default">
                              <div class="panel-heading">
                                <h4 class="panel-title">
                                  <a data-toggle="collapse" data-parent="#accordion" href="#collapse' . $rtId . '">
                                 <center><b>' . $rtName . '</b></center></a>
                                </h4>
                              </div>
                              <div id="collapse' . $rtId . '" class="panel-collapse collapse">
                                <div class="panel-body">';
                            //echo '<center><u><h3>' . $rtName . ' : </h3></u></center>';
                            echo '<u><h4> Relations sortantes :</h4></u>';
                            $nodesFromOutRelations = getNodesIdsFromOutRelations($allOutRelations, $rtId);
                            displayNodesNames($allNodes, $nodesFromOutRelations, $rtId);
                            if ($allInRelations != null) {
                                echo '<u><h4> Relations entrantes :</h4></u>';
                                $nodesFromInRelations = getNodesIdsFromInRelations($allInRelations, $rtId);
                                displayNodesNames($allNodes, $nodesFromInRelations, $rtId);
                            }
                            echo '</div>
                                </div>
                              </div>
                             </div>';
                        }
                        // saving captured output to file
                        file_put_contents($filename, ob_get_contents());
                        // end buffering and displaying page

                        ob_end_flush();
                    }
                }
            }
            ?>

        </div>
        <script src="./js/jquery.min.js"></script>
        <script src="./js/jquery-ui.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script type="text/javascript" src="./js/autocomplete.js"></script> 
        <script type="text/javascript" src="./js/navbar.js"></script>
    </body>
</html>
