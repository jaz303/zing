<?php
namespace admin;

class AssetDialogController extends \zing\cms\admin\BaseController
{
    public function _config() {
        $this->render('json', array(
            'adapters' => zing_config('zing.cms.assets.dialog.adapters'),
            'profiles' => \zing\cms\assets\WarpConfig::load()->profile_index()
        ));
    }
}
?>