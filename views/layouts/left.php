<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\USER;
use app\models\Roles;
use app\models\Menus;
use app\models\Tables;
use app\models\Habilitation;

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <?php

        $compte = USER::findIdentity(Yii::$app->user->identity->IDUSER);

        $habilations = Habilitation::find()->where(['CODEROLE'=>$compte->ROLE])->all(); $menu = array();

        $menu[] = ['label' => 'Menu administration', 'options' => ['class' => 'header']];
        $menu[] = ['label' => 'Tableau de bord', 'icon' => 'dashboard', 'url' => ['site/index']];

        $org = array("M20","M21","M8");
        $cong = array("M4","M5","M14","M7","M9","M11","M19");
        $rh = array("M3","M10","M12");
        $secure = array("M2","M17","M16","M6","M13","M18","M22");

        $menu_org = array(); $menu_cong = array(); $menu_rh = array(); $menu_sec = array(); $menu_other = array();

        foreach($habilations as $habilation) {

            $menus = Menus::findOne($habilation->CODEMENU); $table = Tables::findOne($menus->CODETABLE);

            if(in_array($menus->CODE,$org)) {
                $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];
                $menu_org[] = ['label'=>$menus->LIBELLE,'url' => $lien];
            }

            else if(in_array($menus->CODE,$cong)) {
                $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];
                $menu_cong[] = ['label'=>$menus->LIBELLE,'url' => $lien];
            }

            else if(in_array($menus->CODE,$rh)) {

                if($menus->CODE  == "M12") {
                    $lien = ['site/page'];
                }
                else $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];

                $menu_rh[] = ['label'=>$menus->LIBELLE,'url' => $lien];
            }

            else if(in_array($menus->CODE,$secure)) {

                if($menus->CODE  == "M6") {
                    $lien = [strtolower($table->LIBELLE) . '/update', 'table' => $table->CODE,'id'=>1];
                }
                else $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];

                $menu_sec[] = ['label'=>$menus->LIBELLE,'url' => $lien];
            }

           /* else if($menus->CODE  == "M16") {

                $menu2 = array();

                $lien = ['site/employes']; $lien1 = ['site/services']; $lien2 = ['site/annee'];

                $menu2[] = ['label'=>'Par employe','url' => $lien];

                $menu2[] = ['label'=>'Par service','url' => $lien1];

                $menu2[] = ['label'=>'Par annee','url' => $lien1];

                $menu[] = ['label'=>$menus->LIBELLE,'icon'=>'file-code-o','url' => '#', 'items'=>$menu2];


            } */

            else {
               /* $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];
                $menu[] = ['label' => $menus->LIBELLE, 'icon' => 'file-code-o', 'url' => $lien];*/

                $lien = [strtolower($table->LIBELLE) . '/index', 'table' => $table->CODE];
                $menu_other[] = ['label'=>$menus->LIBELLE,'url' => $lien];
            }
        }

        if(count($menu_org) != 0) {
            $menu[] = ['label'=>"Organigramme",'icon'=>'file-code-o','url' => '#', 'items'=>$menu_org];
        }

        if(count($menu_cong) != 0) {
            $menu[] = ['label'=>"Congés et permissions",'icon'=>'file-code-o','url' => '#', 'items'=>$menu_cong];
        }

        if(count($menu_rh) != 0) {
            $menu[] = ['label'=>"Ressources Humaines",'icon'=>'file-code-o','url' => '#', 'items'=>$menu_rh];
        }

        if(count($menu_sec) != 0) {
            $menu[] = ['label'=>"Sécurité",'icon'=>'file-code-o','url' => '#', 'items'=>$menu_sec];
        }

        if(count($menu_other) != 0) {
            $menu[] = ['label'=>"Autres",'icon'=>'file-code-o','url' => '#', 'items'=>$menu_other];
        }

        ?>

        <?php
        $menu[] = ['label' => 'Téléchargement', 'icon' => 'download', 'url' => ['telechargement/index']];
        ?>

        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => $menu,
            ]
        ) ?>


    </section>

</aside>
