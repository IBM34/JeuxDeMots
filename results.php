<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" /> 
        <title>JDMClient</title>
    </head>

    <body>

        <div id="Resultats">

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

                echo "FROM CACHE :";
                readfile($filename); //affichage du fichier html dans le navigateur
            }

            function getResultsFromServer() {
                $url = 'http://www.jeuxdemots.org/rezo-dump.php?gotermsubmit=Chercher&';
                $data = array(
                    'gotermrel' => utf8_decode($_GET["terme"])
                );
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => http_build_query($data)
                    )
                );
                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context); // recupération des données depuis le serveur.
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

            function displayNodesNames($allNodes, &$nodesArray) {
                echo '<br /> Nombre de termes: ' . count($nodesArray) . '<br /><br />';
                foreach ($nodesArray as $key => $value) {
                    echo '<a title="id=' . $key . ' poids= ' . $value . '" href="./results.php?terme=' . $allNodes[$key] . '&relationType=' . $_GET["relationType"] . '">' . $allNodes[$key] . '</a>';
                    echo ' | ';
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
                    if ($line != '') {
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
                    if ($line != '') {
                        if ($relation[4] == $rtid) { // si le type correspond
                            $nodes[$relation[2]] = $relation[5]; // ajout id + poid du noeud au tableau de noeuds
                        }
                    }
                }
                $nodes = sortNodesByDescWeight($nodes);
                return $nodes;
            }

            $filename = './CACHE/' . $_GET["terme"] . '.html';

            if (file_exists($filename)) { //si la recherche a déja été effectuée par le passé, on utilise le cache.
                getResultsFromCache($filename);
            } else { // sinon on envoi une requête au serveur pour recuperer les données et les traiter.
                // Start output buffering
                ob_start();

                $ServerResults = getResultsFromServer(); // requête au serveur
                $ServerResults = print_r(utf8_encode($ServerResults), true);
                
                $allNodes = getAllNodesFromResults($ServerResults);
                $allOutRelations = getAllOutRelationsFromResults($ServerResults);
                $allInRelations = getAllInRelationsFromResults($ServerResults);
                $allRelationTypes = getAllRelationTypesFromResults($ServerResults);

                echo '<center><u><h1>Résultats</h1></u></center>';
                echo '<hr>';
                echo '<center><u><h3>Définitions</h3></u></center>';
                displayDefinitions($ServerResults);
                echo '<br/>';

                $rtId = $_GET["relationType"]; // Recupération du type de relation choisi dans le formulaire
                if ($rtId == "-1") { // -1 = tous les types de relations
                    foreach ($allRelationTypes as $id => $rtName) { // Pour chaque types de relation
                        echo '<hr>';
                        echo '<center><u><h3>' . $rtName . ' : </h3></u></center>';
                        echo '<u><h4> Relations sortantes : </h4></u>';
                        $nodesFromOutRelations = getNodesIdsFromOutRelations($allOutRelations, $id);
                        displayNodesNames($allNodes, $nodesFromOutRelations);
                        echo '<u><h4> Relations entrantes :</h4></u>';
                        $nodesFromInRelations =  getNodesIdsFromInRelations($allInRelations, $id);
                        displayNodesNames($allNodes,$nodesFromInRelations);
                    }
                } else {
                    $rtName = $allRelationTypes[$rtId];
                    echo '<hr>';
                    echo '<center><u><h3>' . $rtName . ' : </h3></u></center>';
                    echo '<u><h4> Relations sortantes :</h4></u>';
                    $nodesFromOutRelations = getNodesIdsFromOutRelations($allOutRelations, $rtId);
                    displayNodesNames($allNodes, $nodesFromOutRelations);
                    echo '<u><h4> Relations entrantes :</h4></u>';
                    $nodesFromInRelations = getNodesIdsFromInRelations($allInRelations, $rtId);
                    displayNodesNames($allNodes, $nodesFromInRelations);
                }
                // saving captured output to file
                file_put_contents($filename, ob_get_contents());
                // end buffering and displaying page
                 
                ob_end_flush();
            }
            ?>

        </div>

    </body>
</html>
