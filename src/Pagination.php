<?php

/*
 * Classe permettant de générer une barre de pagination
 * Très largement inspirée de l'exemple proposé dans le livre : 
 *   PHP Cookbook (2nd Edition), par Adam Trachtenberg et David Sklar, O'Reilly (2006) 
 * D'importantes modifications ont été apportées au code initial, telles que : 
 * - le regroupement des 2 fonctions du livre dans une classe 
 * - la possibilité de passer la page d'appel aux 2 méthodes, ceci afin de faciliter 
 *   la réutilisation de ces 2 méthodes sur différentes pages 
 * - il a été nécessaire d'ajouter un tableau $params permettant de transmettre d'une 
 *   page à l'autre des paramètres autres que l'offset, tels que les critères de 
 *   sélection saisis sur un formulaire de recherche par exemple.
 * - le nombre de pages directement "appelables" a été limité à 5, des points de suspension
 *   sont ajoutés ensuite, et le lien vers la dernière page est ajouté en fin de barre de 
 *   pagination (la version initiale proposait un lien vers chaque page, ce qui
 *   donnait des résultats particulièrement laids sur des jeux de données de grande taille.
 * - la prise en compte des spécificités de l'architecture MVC dans la manière
 *   de générer l'URL de chacun des éléments de la barre de navigation
 * - la possibilité de générer une barre de pagination selon le style de Bootstrap
 * 
 * Exemple d'utilisation : 
 *       $barre_pagination = new \Pagination($nblines['comptage'], $offset, 
 *                                 $nbl_par_page, '', array(), true); 
 */

class Pagination {

    private $total ;
    private $offset ;
    private $by_page ;
    private $curpage ;
    private $parmpage ;
    private $mvc;
    
    /**
     * Contructeur d'une barre de pagination
     * @param integer $total
     * @param integer $offset
     * @param integer $by_page
     * @param string $curpage
     * @param array $parmpage
     * @param boolean $mvc
     */
    public function __construct($total, $offset, $by_page, $curpage, $parmpage, $mvc) {
        $this->total = (int)$total;
        $this->offset = (int)$offset;
        $this->by_page = (int)$by_page;
        $this->curpage = (string)$curpage;
        $this->parmpage = (is_array($parmpage))?$parmpage:array();
        $this->mvc = (boolean)$mvc ;
    }
    
    private function printLink($inactive, $text, $offset, $current_page, $params_page, $bootstrap) {
        // on prépare l'URL avec tous les paramètres sauf "offset"
        if (!isset($offset) or $offset == '' or $offset == '0') {
            $offset = '1';
        }
        $url = '';
        $params_page ['offset'] = $offset;
        if ($this->mvc) {
            $url = implode('/',$params_page);             
        } else {
            $url = '?' . http_build_query($params_page);            
        }
        $output = '' ;
        $current_page = htmlentities($current_page) ;
        if (!$bootstrap) {
            if ($inactive) {
                $output = "<span class='inactive'>$text</span>".PHP_EOL;
            } else {
                $output = "<span class='active'>" . "<a href='" . 
                        $current_page . $url. "'>$text</a></span>".PHP_EOL;
            } 
        } else {
            if ($inactive) {
                $output = "<li class='disabled'><a href='#'>$text</a></li>".PHP_EOL;
            } else {
                $output = "<li class='activex'><a href='" . 
                        $current_page . $url. "'>$text</a></li>".PHP_EOL;
            }             
        }
        return $output ;
    }

    private function indexedLinks($total, $offset, $by_page, $curpage, $parmpage, $bootstrap) {
        $separator = ' | ';
        $list_links = [];

        $list_links [] = self::printLink($offset == 1, '<< Pr&eacute;c.', $offset - $by_page, $curpage, $parmpage, $bootstrap);

        $compteur = 0;
        $top_suspension = false;

        // affichage de tous les groupes à l'exception du dernier
        for ($start = 1, $end = $by_page; $end < $total; $start += $by_page, $end += $by_page) {
            $compteur += 1;
            if ($compteur < 5) {
                if (!$bootstrap) {
                    $list_links [] = $separator;
                }
                $list_links [] = self::printLink($offset == $start, "$start-$end", $start, $curpage, $parmpage, $bootstrap);
            } else {
                if (!$top_suspension) {
                    $top_suspension = true;
                    if (!$bootstrap) {
                        $list_links [] = ' | ... ';
                    } else {
                        $list_links [] = '<li class="disabled"><a href="#"> ... </a></li>';                        
                    }
                }
            }
        }

        $end = ($total > $start) ? '-' . $total : '';

        if (!$bootstrap) {
            $list_links [] = $separator;
        }
        $list_links [] = self::printLink($offset == $start, "$start$end", $start, $curpage, $parmpage, $bootstrap);

        if (!$bootstrap) {
            $list_links [] = $separator;
        }
        $list_links [] = self::printLink($offset == $start, 'Suiv. >>', $offset + $by_page, $curpage, $parmpage, $bootstrap);
        
        return $list_links;
    }

    public function navBarText() {
        $links = self::indexedLinks($this->total, $this->offset, $this->by_page, $this->curpage, $this->parmpage, false);
        return implode('', $links);
    }

    public function navBarBootstrap() {
        $links = self::indexedLinks($this->total, $this->offset, $this->by_page, $this->curpage, $this->parmpage, true);
        $html = '<nav><ul class="pagination">'.PHP_EOL;
        $html .= implode('', $links);
        $html .= '</ul></nav>'.PHP_EOL;
        return $html;
    }
}
