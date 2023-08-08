<?php
/**
 * Avec ce plugin, le contenu d'un fichier doit  être déclarer AINSI : {un-fichier|type:tckit|taille:20 Mo|auteur:Sébastien LHUILLIER|date:20/09/2013|url:__DOCUMENT/fic.pdf|libelle:ceci est le libellé du fichier|ref-interne:39054-4}
 */
	defined('_JEXEC') or die('Access deny');

	class plgContentFormatFichier extends JPlugin //Concatener à "plg" le nom du groupe (ici Content) puis le nom du plugin ( que l'on trouve ds le XML ligne extension) : plg<Plugin Group><Plugin name>
	{
		function onContentPrepare($content, $article, $params, $limit){	
			$doc = JFactory::getDocument();
			$doc->addStyleSheet('plugins/content/formatfichier/style.css');
			
			/**********************************************
			 * 1. Cette premiere partie ci-dessous permet de remplacer DANS L'ARTICLE la déclation du fichier sous la forme : 
			 * {un-fichier|type:tckit|taille:20 Mo|auteur:Sébastien LHUILLIER|date:20/09/2013|url:__DOCUMENT/fic.pdf|libelle:ceci est le libellé du fichier|ref-interne:39054-4}
			 * en liste <li> utilisée dans le modele de fiches .
			 * 2. Afin de pouvoir lancer une requete avec la recherche, le prefixe toutes les balises par {synthese
			 **/
			$re = '/\{synthese-un-fichier.*type:(.*)\|taille:(.*)\|auteur:(.*)\|date:(.*)\|url:(.*)\|libelle:(.*)\|ref-interne:(.*)\}/mi';
			$str = $article->text;
			$subst = "{tip title=\"[[[tiptitreapropos]]]\" content=\"<span class=\"fic-auteur\">[[[auteur]]]</span>: $3<br /><span class=\"poids-fichier\">[[[poids]]]</span> : $2<br /><span class=\"fic-date\">[[[date]]]</span> : $4\"}<a href=\"$5\" target=\"_blank\">$6<span class=\"ref-interne-seo\">$7</span></a>{/tip}<span class=\"poids-fichier\">$2</span>";
			$article->text = preg_replace($re, $subst, $article->text);
			
			
			/****************************************************************************************************
			 * 3. Cette deuxieme partie permet de récupérer la synthese des fichiers  de tout JOOMLA déclarés comme ci-dessus
			 * pour en sortir une liste SANS DOUBLONS D'URL (on se base sur les contenus des fiches mais filtrés pour l'afficher 
			 * qu'une seule fois les donnés pour une URL donnée
			 ****************************************************************************************************/
			 
			$sql = "SELECT id,title,introtext FROM `#__content` WHERE `introtext` LIKE '%{synthese-un-fichier%' and state=1";
			$db = JFactory::getDBO();
			$db->setQuery($sql);
			$articles = $db->loadObjectList();//retourne un array
			/**************************************
			 * 4. Je déclare un tab ou je vais stocker par type, les résultats trouvés dans les articles
			 ******************************************************/
			 $F = array();
			
			$re = '/\{synthese-un-fichier.*type:(.*)\|taille:(.*)\|auteur:(.*)\|date:(.*)\|url:(.*)\|libelle:(.*)\|ref-interne:(.*)\}/mi';
			
			$i=0;
			
			/*****************************************************************************
			 * Je prends tous les articles un à un rertrournés par la requete SQL pour remplacer le contenu déclaré sous la forme
			 * {synthese-un-fichier|type:tckit|taille:10 Mo|auteur:Sébastien LHUILLIER|date:20/09/2013|url:__DOCUMENT/fic.pdf|libelle:ceci est le libellé du fichier|ref-interne:39054-4}
			 ******************************************************************************/
			 //echo "test : ca liste PLUSIEURS articles !!!";
			 
			foreach($articles as $unArticle)
			{
				
				$str = $unArticle->introtext;
				preg_match_all($re, $unArticle->introtext, $matches, PREG_SET_ORDER, 0);
		
				foreach($matches as $M)
				{
					
					//Je stocke tous les resulttats dans un array à deux dimensions
					$F[$i][0] = $unArticle->id;
					$F[$i][0] = $M[0]; 
					$F[$i][1] = $M[1]; //Type de fichier
					$F[$i][2] = $M[2]; //Poids
					$F[$i][3] = $M[3]; //Auteur
					$F[$i][4] = $M[4]; //Date de creation du document
					$F[$i][5] = $M[5]; //Lien
					$F[$i][6] = $M[6]; //Libellé
					$F[$i][7] = $M[7]; //Ref mat (ex : 39052-4)
					$F[$i][8] = $M[8]; //VIDE ! 
					$i++;
					$article->text = preg_replace($re, $subst, $c);
				}
			}
			
			
			//Mise en forme des résultats
			foreach($F as $unRes)
			{
				echo '<div class="un-element-deformate">';
				echo '<div class="libelle">'.$unRes[6].'</div>';
				echo '<div class="reference">'.$unRes[7].'</div>';
				echo '<div class="type">'.$unRes[1].'</div>';
				echo '<div class="poids">'.$unRes[2].'</div>';
				echo '<div class="date">'.$unRes[4].'</div>';
				echo '<div class="auteur">'.$unRes[3].'</div>';
				echo '<div class="url"><a href="'.$unRes[5].'" target="_blank">'.$unRes[5].'</a></div>';
				echo "</div>";
			}			
		}	
	}


	

