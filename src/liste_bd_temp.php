<?php

function getListeBD() {
    $listebd = array();
    $listebd[] = array(
        'id' => 1,
        'Album' => 'Garulfo',
        'Auteur' => 'Ayroles, Maïorana, Leprévost',
        'Editeur' => 'Delcourt'
    );
    $listebd[] = array(
        'id' => 2,
        'Album' => 'horologiom',
        'Auteur' => 'fabrice Lebeault',
        'Editeur' => 'Delcourt'
    );
    $listebd[] = array(
        'id' => 3,
        'Album' => 'Le château des étoiles',
        'Auteur' => 'Alex Alice',
        'Editeur' => 'Rue De Sevres'
    );
    $listebd[] = array(
        'id' => 4,
        'Album' => 'Le voyage extraordinaire',
        'Auteur' => 'Camboni, Filippi',
        'Editeur' => 'Vents d\'Ouest'
    );

    return $listebd;
}
