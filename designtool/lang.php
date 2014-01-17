<?php
$lang = $_REQUEST['lang'];
echo '<LANG>';
if($lang=="en"){
	echo '<SAVEDATA_MSG>Saving data please wait..</SAVEDATA_MSG>
		<SAVEIMG_MSG1>Saving</SAVEIMG_MSG1>
		<SAVEIMG_MSG2>image please wait..</SAVEIMG_MSG2>
		<ADDNOTE_MSG>Note added successfully...</ADDNOTE_MSG>
		<ADDNOTE_WRN>Add some text...</ADDNOTE_WRN>;
		<REGISTERFNAME_WRN>Please Enter First Name</REGISTERFNAME_WRN>
		<REGISTERLNAME_WRN>Please Enter Last Name</REGISTERLNAME_WRN>
		<REGISTEREMAIL_WRN1>Please Enter Valid Email</REGISTEREMAIL_WRN1>
		<REGISTEREMAIL_WRN2>Email Should Not be Blank</REGISTEREMAIL_WRN2>
		<REGISTERPWORD_WRN1>Password Must Not be Blank</REGISTERPWORD_WRN1>
		<REGISTERPWORD_WRN2>Please Confirm Password</REGISTERPWORD_WRN2>
		<REGISTERPWORD_WRN3>Password Must Not less than 6 Characters</REGISTERPWORD_WRN3>
		<REGISTERPWORD_WRN4>Password Not Match</REGISTERPWORD_WRN4>
		<REGISTEREMAIL_MSG>Email address already exist.</REGISTEREMAIL_MSG>
		<LOGIN_WRN>Invalid Username/Password</LOGIN_WRN>
		<LOGIN_WRN1>Invalid Email Address/Password</LOGIN_WRN1>
		<LOGIN_MSG>You have successfully logged in. You can now upload images.</LOGIN_MSG>
		<QUOTE_WRN>Please enter quantity</QUOTE_WRN>
		<QUOTE_WRN_1>Some of the products cannot be ordered in requested quantity.</QUOTE_WRN_1>
		<QUOTE_MSG>Updating...</QUOTE_MSG>
		<UPLOAD_CONFIRM>To save image in your account please login first. Do you want to login ?</UPLOAD_CONFIRM>
		<NOTFOUND_MSG>No Items Found.</NOTFOUND_MSG>
		<BRINGTOFRONT>Bring To Front</BRINGTOFRONT>
		<SENDTOBACK>Send To Back</SENDTOBACK>
		<DUPLICATE>Duplicate</DUPLICATE>
		<TRASH>Trash</TRASH>
		<TRASH_MSG>Are you sure you want to trash design?</TRASH_MSG>
		<ZOOM>Zoom</ZOOM>
		<PREVIEW>Preview</PREVIEW>
		<DETAIL>Detail</DETAIL>
		<HELPTIP>Help</HELPTIP>
		<SAVETIP>Save</SAVETIP>
		<FBTIP>Share on FB</FBTIP>
		<TWITTERTIP>Share on Twitter</TWITTERTIP>
		<PINTRESTTIP>Share on Pinterest</PINTRESTTIP>
		<SHARE>Share via Email</SHARE>
		<BACKTOSTORE>Save your work for later use! Are you sure you want to leave this page?</BACKTOSTORE>
		<SENDTOFRIEND>Your design has been sent to your friend successfully.</SENDTOFRIEND>
		<SAVE_WRN>Please enter design name.</SAVE_WRN>
		<SAVE>Your design is saved successfully. You can access it in My Account -> My Designs</SAVE>
		<SAVE_FAIL_WARN>Sorry!!! currently we are not able to fulfill your request. Please try later.</SAVE_FAIL_WARN>
		<SOURCEFILE_MSG>HD file uploaded successfully.</SOURCEFILE_MSG>
		<FRONT>FRONT</FRONT>
		<BACK>BACK</BACK>
		<LEFT>LEFT</LEFT>
		<RIGHT>RIGHT</RIGHT>
		<INSIDE>INSIDE</INSIDE>
		<SEARCH_TXT>Search....</SEARCH_TXT>
		<UNIT_PRICE>Avg. Unit Price: </UNIT_PRICE>
		<SELECT_PRODUCT_MSG>Please Select Product Category from the left Pane.</SELECT_PRODUCT_MSG>
		<OUTPUT_GENERATE_COMPLETE_MSG>Images are generated and attached to the order.</OUTPUT_GENERATE_COMPLETE_MSG>
		<ACCESS_DESIGN_PROMPT>Do you want to access only design?</ACCESS_DESIGN_PROMPT>
		<ALIGN_LEFT>Align Left</ALIGN_LEFT>
		<ALIGN_RIGHT>Align Right</ALIGN_RIGHT>
		<ALIGN_TOP>Align Top</ALIGN_TOP>
		<ALIGN_BOTTOM>Align Bottom</ALIGN_BOTTOM>
		<ALIGN_HCENTER>Align Horizontal Center</ALIGN_HCENTER>
		<ALIGN_VCENTER>Align Vertical Center</ALIGN_VCENTER>
		<ALIGN_CENTER>Align Center</ALIGN_CENTER>
		<ALIGN_TOPLEFT>Align Top Left</ALIGN_TOPLEFT>
		<ALIGN_TOPRIGHT>Align Top Right</ALIGN_TOPRIGHT>
		<ALIGN_BOTTOMLEFT>Align Bottom Left</ALIGN_BOTTOMLEFT>
		<ALIGN_BOTTOMRIGHT>Align Bottom Right</ALIGN_BOTTOMRIGHT>
		<MOVE_LEFT>Move Left</MOVE_LEFT>
		<MOVE_RIGHT>Move Right</MOVE_RIGHT>
		<MOVE_UP>Move Up</MOVE_UP>
		<MOVE_DOWN>Move Down</MOVE_DOWN>
		<MINIMUM>Min</MINIMUM>
		<MAXIMUM>Max</MAXIMUM>
		<NO_PRODUCT_FOUND>No Products Found</NO_PRODUCT_FOUND>
		<COLORS_ON>Colors on </COLORS_ON>
		<UNDO>Undo</UNDO>
		<REDO>Redo</REDO>
		<HELP>
			<PROUDCT TYPE="IMAGE">help/chooseaproduct.png</PROUDCT>
			<PICKCOLOR TYPE="IMAGE">help/chooseacolor.png</PICKCOLOR>
			<ADDART TYPE="IMAGE">help/chooseanart.png</ADDART>
			<ADDTEXT TYPE="IMAGE">help/addtext.png</ADDTEXT>
			<ADDIMAGE TYPE="IMAGE">help/addimage.png</ADDIMAGE>
			<NAMENUMBER TYPE="IMAGE">help/namennumber.png</NAMENUMBER>
			<ADDNOTE TYPE="IMAGE">help/addnote.png</ADDNOTE>
			<MYDESIGNS TYPE="IMAGE">help/mydesigns.png</MYDESIGNS>
			<DESIGNIDEAS TYPE="IMAGE">help/designidea.png</DESIGNIDEAS>
			<RESTOREPANEL TYPE="TEXT">minimize/maximize the panel</RESTOREPANEL>
			<UNDO TYPE="TEXT">Undo your last action</UNDO>
			<REDO TYPE="TEXT">Redo your last removed action</REDO>
			<DETAIL TYPE="TEXT">View product features and detailed description.</DETAIL>
			<PREVIEW TYPE="TEXT">Preview your design before placing order.</PREVIEW>
			<ZOOM TYPE="TEXT">Zoom-in, Zoom-out design area.</ZOOM>
			<HELP TYPE="TEXT">Start interactive help.</HELP>
			<TRASH TYPE="TEXT">Clear all work done and start afresh.</TRASH>
			<SAVE TYPE="TEXT">Save your design for later use.</SAVE>
			<SHARE_VIA_EMAIL TYPE="TEXT">Share your design with a friend via e-mail.</SHARE_VIA_EMAIL>
			<SHARE_ON_FB TYPE="TEXT">Share your design on Facebook.</SHARE_ON_FB>
			<SHARE_ON_TWITTER TYPE="TEXT">Share your design on Twitter.</SHARE_ON_TWITTER>
			<SHARE_ON_PINTEREST TYPE="TEXT">Share your design on Pinterest.</SHARE_ON_PINTEREST>
			<LAYER_PANEL TYPE="TEXT"> The layers panel is invaluable for managing complex designs. For example, you might find it difficult to select a specific object among a group of overlapping objects, but the Layers panel makes it easy to select the object and position it in the object stack or change the visibility state.</LAYER_PANEL>
			<QTY_SIZES TYPE="TEXT"> Mention the different sizes and quantity for each required size you want to order. The live quote feature gives you instant quote based on your artwork design and no. of colors used in each side of the product. </QTY_SIZES>
			<COLORS_USED TYPE="TEXT"> The printing cost may be determined by the no. of different color used in your artwork design. Hence this panel keeps track of all individual colors used by you while creating your design for each side. Yes, this feature does not account for the no. of colors in uploaded/imported photos and images. It just works on color options available for decoration of text and clipart.</COLORS_USED>
			<CHOOSE_SIDE TYPE="IMAGE">help/chooseside.png</CHOOSE_SIDE>
			<DESIGN_AREA TYPE="IMAGE">help/designarea.png</DESIGN_AREA>
		</HELP>';
}else if($lang=="de"){
	echo '<SAVEDATA_MSG>Gegevens opslaan, even geduld aub..</SAVEDATA_MSG>
		<SAVEIMG_MSG1>Opslaan</SAVEIMG_MSG1>
		<SAVEIMG_MSG2>ontwerp even geduld aub..</SAVEIMG_MSG2>
		<ADDNOTE_MSG>Notitie succesvol toegevoegd...</ADDNOTE_MSG>
		<ADDNOTE_WRN>Voeg tekst toe...</ADDNOTE_WRN>;
		<REGISTERFNAME_WRN>Vul aub Voornaam</REGISTERFNAME_WRN>
		<REGISTERLNAME_WRN>Vul aub Achternaam</REGISTERLNAME_WRN>
		<REGISTEREMAIL_WRN1>Vul een geldig email</REGISTEREMAIL_WRN1>
		<REGISTEREMAIL_WRN2>E-mail mag niet leeg zijn</REGISTEREMAIL_WRN2>
		<REGISTERPWORD_WRN1>Wachtwoord mag niet leeg zijn</REGISTERPWORD_WRN1>
		<REGISTERPWORD_WRN2>Gelieve Conform Wachtwoord</REGISTERPWORD_WRN2>
		<REGISTERPWORD_WRN3>Wachtwoord mag niet minder dan 6 tekens</REGISTERPWORD_WRN3>
		<REGISTERPWORD_WRN4>Wachtwoord komt niet overeen</REGISTERPWORD_WRN4>
		<REGISTEREMAIL_MSG>E-mail adres bestaat reeds</REGISTEREMAIL_MSG>
		<LOGIN_WRN>Ongeldige gebruikersnaam / wachtwoord</LOGIN_WRN>
		<LOGIN_WRN1>Ongeldig e-mailadres / wachtwoord</LOGIN_WRN1>
		<LOGIN_MSG>U hebt ingelogd U kunt nu afbeeldingen uploaden.</LOGIN_MSG>
		<QUOTE_WRN>Vul de hoeveelheid in</QUOTE_WRN>
		<QUOTE_WRN_1>Sommige van de producten kunnen niet worden besteld in gevraagde hoeveelheid.</QUOTE_WRN_1>
		<QUOTE_MSG>bijwerken...</QUOTE_MSG>
		<UPLOAD_CONFIRM>Om beeld op te slaan in uw account, log dan eerst in. Wilt u zich aanmelden?</UPLOAD_CONFIRM>
		<NOTFOUND_MSG>Geen items gevonden.</NOTFOUND_MSG>
		<BRINGTOFRONT>Breng geselecteerd tekst/afb. naar voren</BRINGTOFRONT>
		<SENDTOBACK>Breng geselecteerd tekst/afb. naar achteren</SENDTOBACK>
		<DUPLICATE>Duplicaat</DUPLICATE>
		<TRASH>Prullenbak</TRASH>
		<TRASH_MSG>Weet u zeker dat u het ontwerp in de prullenbak wilt deponeren?</TRASH_MSG>
		<ZOOM>Zoom</ZOOM>
		<PREVIEW>Voorbeeld</PREVIEW>
		<DETAIL>Detail</DETAIL>
		<HELP>Help</HELP>
		<SAVETIP>Bewaar</SAVETIP>
		<FBTIP>Share on FB</FBTIP>
		<TWITTERTIP>Share on Twitter</TWITTERTIP>
		<PINTRESTTIP>Share on Pinterest</PINTRESTTIP>
		<SHARE>Delen via E-mail</SHARE>
		<BACKTOSTORE>Sla uw werk op voor later gebruik! Weet u zeker dat u deze pagina wilt verlaten?</BACKTOSTORE>
		<SENDTOFRIEND>Uw ontwerp is met succes verzonden naar uw contact.</SENDTOFRIEND>
		<SAVE_WRN>Vul de naam van het ontwerp.</SAVE_WRN>
		<SAVE>Uw ontwerp is succesvol opgeslagen. U heeft er toegang toe bij My acount / ophalen Ontwerp</SAVE>
		<SAVE_FAIL_WARN>Sorry! momenteel zijn we niet in staat om uw verzoek te voldoen. Probeer het later opnieuw.</SAVE_FAIL_WARN>
		<SOURCEFILE_MSG>HD-bestand geüpload.</SOURCEFILE_MSG>
		<FRONT>VOOR</FRONT>
		<BACK>ACHTER</BACK>
		<LEFT>LINKS</LEFT>
		<RIGHT>RECHTS</RIGHT>
		<INSIDE>INSIDE</INSIDE>
		<SEARCH_TXT>zoeken....</SEARCH_TXT>
		<UNIT_PRICE>gemiddelde eenheidsprijs </UNIT_PRICE>
		<SELECT_PRODUCT_MSG>Selecteer Product Categorie in het linkerdeelvenster.</SELECT_PRODUCT_MSG>
		<OUTPUT_GENERATE_COMPLETE_MSG>Beelden worden gegenereerd en aan de orde.</OUTPUT_GENERATE_COMPLETE_MSG>
		<ACCESS_DESIGN_PROMPT>Wilt u alleen het ontwerp te openen?</ACCESS_DESIGN_PROMPT>
		<ALIGN_LEFT>Links uitlijnen</ALIGN_LEFT>
		<ALIGN_RIGHT>Rechts uitlijnen</ALIGN_RIGHT>
		<ALIGN_TOP>boven uitlijnen</ALIGN_TOP>
		<ALIGN_BOTTOM>Onder uitlijnen</ALIGN_BOTTOM>
		<ALIGN_HCENTER>Uitlijnen Horizontale Center</ALIGN_HCENTER>
		<ALIGN_VCENTER>Lijn Verticale Center</ALIGN_VCENTER>
		<ALIGN_CENTER>Centreren</ALIGN_CENTER>
		<ALIGN_TOPLEFT>Boven uitlijnen Links</ALIGN_TOPLEFT>
		<ALIGN_TOPRIGHT>Boven uitlijnen Rechts</ALIGN_TOPRIGHT>
		<ALIGN_BOTTOMLEFT>Onder uitlijnen Links</ALIGN_BOTTOMLEFT>
		<ALIGN_BOTTOMRIGHT>Onder uitlijnen Rechts</ALIGN_BOTTOMRIGHT>
		<MOVE_LEFT>Naar links</MOVE_LEFT>
		<MOVE_RIGHT>Naar rechts</MOVE_RIGHT>
		<MOVE_UP>omhoog</MOVE_UP>
		<MOVE_DOWN>Omlaag</MOVE_DOWN>
		<MINIMUM>Min</MINIMUM>
		<MAXIMUM>Max</MAXIMUM>
		<NO_PRODUCT_FOUND>Geen producten gevonden</NO_PRODUCT_FOUND>
		<COLORS_ON>Colors on </COLORS_ON>
		<UNDO>Undo</UNDO>
		<REDO>Redo</REDO>';
}else if($lang=="da"){
	echo '<SAVEDATA_MSG>Gemmer data, vent venligst..</SAVEDATA_MSG>
		<SAVEIMG_MSG1>Gemmer</SAVEIMG_MSG1>
		<SAVEIMG_MSG2>billede, vent venligst..</SAVEIMG_MSG2>
		<ADDNOTE_MSG>Bemærkning er tilføjet...</ADDNOTE_MSG>
		<ADDNOTE_WRN>Tilføj noget tekst...</ADDNOTE_WRN>;
		<REGISTERFNAME_WRN>Skriv venligst dit fornavn</REGISTERFNAME_WRN>
		<REGISTERLNAME_WRN>Skriv venligst dit efternavn</REGISTERLNAME_WRN>
		<REGISTEREMAIL_WRN1>Skriv venligst korrekt Email adresse</REGISTEREMAIL_WRN1>
		<REGISTEREMAIL_WRN2>Email må ikke være blank</REGISTEREMAIL_WRN2>
		<REGISTERPWORD_WRN1>Password må ikke være blank</REGISTERPWORD_WRN1>
		<REGISTERPWORD_WRN2>Bekræft Password</REGISTERPWORD_WRN2>
		<REGISTERPWORD_WRN3>Password skal være på min 6 karakterer</REGISTERPWORD_WRN3>
		<REGISTERPWORD_WRN4>Password er forkert</REGISTERPWORD_WRN4>
		<REGISTEREMAIL_MSG>E-mail adresse findes allerede</REGISTEREMAIL_MSG>
		<LOGIN_WRN>Forkert Brugernavn/Password</LOGIN_WRN>
		<LOGIN_WRN1>Ugyldig e-mailadresse / password</LOGIN_WRN1>
		<LOGIN_MSG>Du er logget ind Du kan nu uploade billeder.</LOGIN_MSG>
		<QUOTE_WRN>Angiv antal</QUOTE_WRN>
		<QUOTE_WRN_1>Nogle af produkterne kan ikke bestilles i ønskede mængde.</QUOTE_WRN_1>
		<QUOTE_MSG>Opdaterer...</QUOTE_MSG>
		<UPLOAD_CONFIRM>Hvis du vil gemme billedet i din konto skal du logge ind først. Ønsker du at logge ind?</UPLOAD_CONFIRM>
		<NOTFOUND_MSG>Ikke fundet.</NOTFOUND_MSG>
		<BRINGTOFRONT>Flyt frem</BRINGTOFRONT>
		<SENDTOBACK>Flyt tilbage</SENDTOBACK>
		<DUPLICATE>Duplicate</DUPLICATE>
		<TRASH>Papirkurv</TRASH>
		<TRASH_MSG>Er du sikke på du vil slette designet?</TRASH_MSG>
		<ZOOM>Zoom</ZOOM>
		<PREVIEW>Eksempel</PREVIEW>
		<DETAIL>Detalje</DETAIL>
		<HELP>Hjælp</HELP>
		<SAVETIP>Gem</SAVETIP>
		<FBTIP>Share on FB</FBTIP>
		<TWITTERTIP>Share on Twitter</TWITTERTIP>
		<PINTRESTTIP>Share on Pinterest</PINTRESTTIP>
		<SHARE>Del via email</SHARE>
		<BACKTOSTORE>Gem dit arbejde til senere brug! Er du sikker på at du vil forlade denne side?</BACKTOSTORE>
		<SENDTOFRIEND>Dit design er nu sendt til din ven.</SENDTOFRIEND>
		<SAVE_WRN>Skriv et navn til dit design.</SAVE_WRN>
		<SAVE>Dit design er gemt korrekt. Du kan få adgang til det fra Min konto / Hent Design</SAVE>
		<SAVE_FAIL_WARN>Undskyld! vi i øjeblikket ikke er i stand til at opfylde din anmodning. Prøv igen senere.</SAVE_FAIL_WARN>
		<SOURCEFILE_MSG>HD-fil uploadet.</SOURCEFILE_MSG>
		<FRONT>For</FRONT>
		<BACK>Bag</BACK>
		<LEFT>Venstre</LEFT>
		<RIGHT>Højre</RIGHT>
		<INSIDE>INSIDE</INSIDE>
		<SEARCH_TXT>søg....</SEARCH_TXT>
		<UNIT_PRICE>gennemsnitlige enhedspris: </UNIT_PRICE>
		<SELECT_PRODUCT_MSG>Vælg produktkategori i venstre rude.</SELECT_PRODUCT_MSG>
		<OUTPUT_GENERATE_COMPLETE_MSG>Billeder genereres og fastgjort til ordren.</OUTPUT_GENERATE_COMPLETE_MSG>
		<ACCESS_DESIGN_PROMPT>Vil du kun adgang design?</ACCESS_DESIGN_PROMPT>
		<ALIGN_LEFT>Venstrejusteret</ALIGN_LEFT>
		<ALIGN_RIGHT>Juster højre</ALIGN_RIGHT>
		<ALIGN_TOP>Juster øverst</ALIGN_TOP>
		<ALIGN_BOTTOM>Juster nederst</ALIGN_BOTTOM>
		<ALIGN_HCENTER>Justér vandret center</ALIGN_HCENTER>
		<ALIGN_VCENTER>Juster lodret centrering</ALIGN_VCENTER>
		<ALIGN_CENTER>Centreret</ALIGN_CENTER>
		<ALIGN_TOPLEFT>Juster øverst til venstre</ALIGN_TOPLEFT>
		<ALIGN_TOPRIGHT>Juster øverst højre</ALIGN_TOPRIGHT>
		<ALIGN_BOTTOMLEFT>Juster nederst til venstre</ALIGN_BOTTOMLEFT>
		<ALIGN_BOTTOMRIGHT>Juster nederst højre</ALIGN_BOTTOMRIGHT>
		<MOVE_LEFT>Flyt til venstre</MOVE_LEFT>
		<MOVE_RIGHT>Flyt til højre</MOVE_RIGHT>
		<MOVE_UP>Flyt op</MOVE_UP>
		<MOVE_DOWN>Flyt ned</MOVE_DOWN>
		<MINIMUM>Min</MINIMUM>
		<MAXIMUM>Max</MAXIMUM>
		<NO_PRODUCT_FOUND>Produktet er ikke fundet</NO_PRODUCT_FOUND>
		<COLORS_ON>Farver på </COLORS_ON>
		<UNDO>Undo</UNDO>
		<REDO>Redo</REDO>';
}else if($lang=="ge"){
	echo '<SAVEDATA_MSG>Speichert Daten, bitte warten...</SAVEDATA_MSG>
		<SAVEIMG_MSG1>Speichert</SAVEIMG_MSG1>
		<SAVEIMG_MSG2>Bild, bitte warten...</SAVEIMG_MSG2>
		<ADDNOTE_MSG>Notiz erfolgreich hinzugefügt...</ADDNOTE_MSG>
		<ADDNOTE_WRN>Füge Text hinzu...</ADDNOTE_WRN>;
		<REGISTERFNAME_WRN>Bitte Vornamen eingeben</REGISTERFNAME_WRN>
		<REGISTERLNAME_WRN>Bitte Nachnamen eingeben</REGISTERLNAME_WRN>
		<REGISTEREMAIL_WRN1>Bitte gültiges Passwort eingeben</REGISTEREMAIL_WRN1>
		<REGISTEREMAIL_WRN2>Email sollte nicht leer sein</REGISTEREMAIL_WRN2>
		<REGISTERPWORD_WRN1>Passwort darf nicht leer sein</REGISTERPWORD_WRN1>
		<REGISTERPWORD_WRN2>Bitte bestätigen Sie das Passwort</REGISTERPWORD_WRN2>
		<REGISTERPWORD_WRN3>Das Passwort muss mindestens 6 Zeichen haben</REGISTERPWORD_WRN3>
		<REGISTERPWORD_WRN4>Passwort stimmt nicht</REGISTERPWORD_WRN4>
		<REGISTEREMAIL_MSG>Emailadresse existiert bereits.</REGISTEREMAIL_MSG>
		<LOGIN_WRN>Invalid Username/Password</LOGIN_WRN>
		<LOGIN_WRN1>Ungültige E-Mail-Adresse / Passwort</LOGIN_WRN1>
		<LOGIN_MSG>Sie haben sich erfolgreich angemeldet Sie können nun Bilder hochladen.</LOGIN_MSG>
		<QUOTE_WRN>Bitte Menge eingeben</QUOTE_WRN>
		<QUOTE_WRN_1>Einige der Produkte können nicht in die angeforderte Menge bestellt werden.</QUOTE_WRN_1>
		<QUOTE_MSG>am aktualisieren...</QUOTE_MSG>
		<UPLOAD_CONFIRM>Um das Bild zu speichern muss man sich erst einloggen. Möchten Sie sich jetzt einloggen?</UPLOAD_CONFIRM>
		<NOTFOUND_MSG>Keine Artikel gefunden.</NOTFOUND_MSG>
		<BRINGTOFRONT>Ganz nach vorne</BRINGTOFRONT>
		<SENDTOBACK>Ganz nach hinten</SENDTOBACK>
		<DUPLICATE>Duplikat</DUPLICATE>
		<TRASH>Papierkorb</TRASH>
		<TRASH_MSG>Willst du das Design wirklich löschen?</TRASH_MSG>
		<ZOOM>Zoom</ZOOM>
		<PREVIEW>Vorschau</PREVIEW>
		<DETAIL>Detail</DETAIL>
		<HELP>Hilfe</HELP>
		<SAVETIP>sparen</SAVETIP>
		<FBTIP>Share on FB</FBTIP>
		<TWITTERTIP>Share on Twitter</TWITTERTIP>
		<PINTRESTTIP>Share on Pinterest</PINTRESTTIP>
		<SHARE>Empfehelen via E-Mail</SHARE>
		<BACKTOSTORE>Speicher deine Arbeit für später! Bist du sicher, dass du die Seite verlassen möchtest?</BACKTOSTORE>
		<SENDTOFRIEND>Dein Design wurde erfolgreich an deinen Freund gesendet.</SENDTOFRIEND>
		<SAVE_WRN>Please enter design name.</SAVE_WRN>
		<SAVE>Ihr Design wurde erfolgreich gespeichert. Du kannst über "Mein Benutzerkonto / Design wiederherstellen" darauf zu greifen</SAVE>
		<SAVE_FAIL_WARN>Es tut uns leid! Momentan sind wir nicht in der Lage, Ihren Wunsch zu erfüllen. Bitte versuchen Sie es später.</SAVE_FAIL_WARN>
		<SOURCEFILE_MSG>HD-Datei erfolgreich hochgeladen.</SOURCEFILE_MSG>
		<FRONT>Vorne</FRONT>
		<BACK>Hinten</BACK>
		<LEFT>Links</LEFT>
		<RIGHT>Rechts</RIGHT>
		<INSIDE>innen</INSIDE>
		<SEARCH_TXT>Suche....</SEARCH_TXT>
		<UNIT_PRICE>durchschnittliche Stückpreis: </UNIT_PRICE>
		<SELECT_PRODUCT_MSG>Bitte wählen Produktkategorie aus dem linken Bereich.</SELECT_PRODUCT_MSG>
		<OUTPUT_GENERATE_COMPLETE_MSG>Bilder erzeugt und an den Auftrag.</OUTPUT_GENERATE_COMPLETE_MSG>
		<ACCESS_DESIGN_PROMPT>Wollen Sie nur das Design zugreifen?</ACCESS_DESIGN_PROMPT>
		<ALIGN_LEFT>Linksbündig</ALIGN_LEFT>
		<ALIGN_RIGHT>Rechtsbündig</ALIGN_RIGHT>
		<ALIGN_TOP>oben ausrichten</ALIGN_TOP>
		<ALIGN_BOTTOM>unten ausrichten</ALIGN_BOTTOM>
		<ALIGN_HCENTER>Horizontal zentriert</ALIGN_HCENTER>
		<ALIGN_VCENTER>Richten Vertikal zentrieren</ALIGN_VCENTER>
		<ALIGN_CENTER>Zentriert</ALIGN_CENTER>
		<ALIGN_TOPLEFT>Richten Sie oben links</ALIGN_TOPLEFT>
		<ALIGN_TOPRIGHT>Richten Sie oben rechts</ALIGN_TOPRIGHT>
		<ALIGN_BOTTOMLEFT>Unten ausrichten Linke</ALIGN_BOTTOMLEFT>
		<ALIGN_BOTTOMRIGHT>Unten ausrichten Rechts</ALIGN_BOTTOMRIGHT>
		<MOVE_LEFT>Nach links</MOVE_LEFT>
		<MOVE_RIGHT>Nach rechts</MOVE_RIGHT>
		<MOVE_UP>Nach oben</MOVE_UP>
		<MOVE_DOWN>Nach unten</MOVE_DOWN>
		<MINIMUM>Min</MINIMUM>
		<MAXIMUM>Max</MAXIMUM>
		<NO_PRODUCT_FOUND>Keine Produkte gefunden</NO_PRODUCT_FOUND>
		<COLORS_ON>Farver på </COLORS_ON>
		<UNDO>Undo</UNDO>
		<REDO>Redo</REDO>';
}
echo "</LANG>";
?>