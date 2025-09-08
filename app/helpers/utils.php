<?php

function dani() {
    return [
        'pon' => 'Ponedjeljak',
        'uto' => 'Utorak',
        'sri' => 'Srijeda',
        'cet' => 'Četvrtak',
        'pet' => 'Petak',
        'sub' => 'Subota',
        'ned' => 'Nedjelja'
    ];
}
function smjene() {
    return [
        'jutro' => 'Jutro',
        'popodne' => 'Popodne',
        'vecer' => 'Večer'
    ];
}


function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

function smjene_sa_vremenima() {
    global $pdo;
    
    $osnovne_smjene = [
        'jutro' => 'Jutro',
        'popodne' => 'Popodne', 
        'vecer' => 'Večer'
    ];
    
    try {
        $stmt = $pdo->prepare("SELECT smjena, pocetak, kraj FROM smjene_vremena WHERE aktivan = 1");
        $stmt->execute();
        $vremena = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $smjene_rezultat = [];
        foreach ($osnovne_smjene as $key => $naziv) {
            $vrijeme_data = null;
            foreach ($vremena as $v) {
                if ($v['smjena'] === $key) {
                    $vrijeme_data = $v;
                    break;
                }
            }
            
            if ($vrijeme_data) {
                $pocetak = date('H:i', strtotime($vrijeme_data['pocetak']));
                $kraj = date('H:i', strtotime($vrijeme_data['kraj']));
                $smjene_rezultat[$key] = "$naziv ($pocetak-$kraj)";
            } else {
                $smjene_rezultat[$key] = "$naziv (vreme nije definisano)";
            }
        }
        
        return $smjene_rezultat;
    } catch (Exception $e) {
        return $osnovne_smjene; // fallback na osnovne nazive
    }
}