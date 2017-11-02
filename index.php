<!DOCTYPE html>
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <title>JDMClient</title>
   </head>
   <body>
      <center>
         <h3>Saisissez un terme :</h3>
         <form action="results.php">
         <div class="form1">
            <dl>
               <input name="terme" type="text" value="" size="35" required /><br />
               <br />	 
	       <select name="relationType">    
	       <option value="-1">Tout</option>
               <?php

		$filename = "relationTypes.txt";
		$file = file_get_contents($filename);

		 foreach(preg_split("/((\r?\n)|(\r\n?))/", $file) as $line){ // parcours chaque ligne
	
			$rt = explode("|", $line);

			if ($line!=''){
	
				$rt[0] = substr($rt[0], 5);
				$rt[1] = substr($rt[1], 6,-1);
                                $rt[2] = substr($rt[2], 12,-1);
			    
				    echo  '<option value="'.$rt[0].'">'.$rt[1].' - '.$rt[2].'</option>';
			}
		    } 

		echo '</select>';

		?>
               <input name="valid" type="submit" id="valid" value="Chercher" class="valid" /></p>
            </dl>
         </div>
      </center>
   </body>
</html>
