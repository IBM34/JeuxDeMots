<?php
function getTextBetweenStrings($startTag, $endTag, $ServerResults) {
                $startsAt = strpos($ServerResults, $startTag) + strlen($startTag);
                $endsAt = strpos($ServerResults, $endTag, $startsAt);
                $result = substr($ServerResults, $startsAt, $endsAt - $startsAt);
                $result = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $result); // suppression des ligne blanches
                return $result;
            }

 function getAllNodesFromResults($in,$out,$content) {
                $startTag = "// -- NODES";
                $endTag = "// -- RELATIONS";
                $nodes = getTextBetweenStrings($startTag, $endTag, $content);
                foreach (preg_split("/((\r?\n)|(\r\n?))/", $nodes) as $line) { // parcours chaque ligne
                    $node = explode("|", $line);
                    if ($line != '') {
                        $node[1] = substr($node[1], 3, -1);
			echo $node[1];
			$node[1] = $node[1].',';
                    file_put_contents($out, $node[1], FILE_APPEND);
                    }
                }
            }
$in = 'dump.txt';
$out ='noeuds.txt';
$content = file_get_contents($in, FILE_USE_INCLUDE_PATH);
$content = print_r(utf8_encode($content), true);
getAllNodesFromResults($in,$out,$content);

?>
