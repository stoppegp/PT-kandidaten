PT-kandidaten
=============

Dieses Wordpress-Plugin stellt eine tabellarische Übersicht von Kandidaten sowie einen Wahlkreisfinder zur Verfügung. Die Einträge können über Wordpress-Tools exportiert und importiert werden.


Kandidaten
----------

Kandidaten können in der Wordpress-Adminoberfläche im Menüpunkt "Kandidaten" angelegt werden. Es kann ein Name und der Wahlkreis sowie ein Link zu Unterstützerformularen angegeben werden.


Kandidatenübersicht
-------------------

Eine tabellarische Übersicht über alle Kandidaten kann mit dem Shortcode `[pt-kandidaten-tabelle]` eingebunden werden.

Es können folgende Parameter verwendet werden:

*   `sort`:       Sortierreihenfolge der Einträge. Mögliche Werte sind `name` und `wknr`.
*   `uu`:         Mit dem Wert `true` wird eine zusätzliche Spalte mit Links zu den UU-Formularen angezeigt. Ansonsten Parameter weglassen.
*   `wahl`:       wird aktuell nicht benötigt, `btw` ist vorausgewählt. Das Plugin ist für die gleichzeitige Verwendung mehrerer Wahlen vorbereitet.

**Beispiel:** `[pt-kandidaten-tabelle sort="wknr" uu="true"]` zeigt eine Tabelle mit Spalte für Formulare sortiert nach der Wahlkreisnummer an.


Wahlkreisfinder
---------------

Mit dem Wahlkreisfinder kann über den Wohnort der gesuchte Wahlkreis gefunden werden. Er wird mit dem Shortcode `[pt-kandidaten-wkf]` eingebunden.

Es können folgende Parameter verwendet werden:

*   `start`:      Vorauswahl eines bestimmten Gebietes, um den Finder z.B. auf ein Bundesland zu beschränken.
                  Der Wert muss exakt dem angezeigten Namen des Gebietes entsprechen. Mehrere Gebiete können kommagetrennt in der passenden Reihenfolge angegeben werden.
*   `wahl`:       wird aktuell nicht benötigt, `btw` ist vorausgewählt. Das Plugin ist für die gleichzeitige Verwendung mehrerer Wahlen vorbereitet.

Die angezeigten Texte können mit folgenden Parametern geändert werden:

*   `text_nok`:   Text, der angezeigt wird, wenn im gefundenen Wahlkreis kein Kandidat existiert
*   `text_nouu`:  Text, der angezeigt wird, wenn im gefundenen Wahlkreis ein Kandidat existiert, aber kein Link zur einem UU-Formular eingetragen ist.
*   `text_uu`:    Text, der angezeigt wird, wenn im gefundenen Wahlkreis ein Kandidat existiert und ein Link zur einem UU-Formular eingetragen ist.

Die Werte für diese Parameter sollten mit einfachen Anführungszeichen eingeklammert werden. Es können folgende Platzhalter verwendet werden:

*   `{wknr}`:     Die Nummer des Wahlkreises
*   `{wkname}`:   Der Name des Wahlkreises
*   `{kandidat}`: Der Name des Kandidaten
*   `{uu}`:       Der Link zum Unterstützerformular

**Beispiel:** Mit dem Shortcode

    [pt-kandidaten-wkf start="Baden-Württemberg,Stuttgart"
    text_nok='Wahlkreis {wknr} {wkname}: Kein Kandidat!'
    text_nouu='Wahlkreis {wknr} {wkname}: {kandidat}'
    text_uu='Wahlkreis {wknr} {wkname}: <a href="{uu}">{kandidat}</a>']

wird ein Wahlkreisfinder nur für den Kreis Stuttgart mit entsprechenden Texten angezeigt.
