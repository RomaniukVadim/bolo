<?php namespace Bolo\Bolo\Behaviors;


use System\Classes\ModelBehavior;

class ThemeEditorSetting extends ModelBehavior
{
    public $defaults;

    public function __construct($model)
    {
        parent::__construct($model);

//        $this->model->bindEvent('model.afterFetch', [$this, 'afterModelFetch']);
        $this->model->bindEvent('model.getAttribute', [$this, 'getSettingsValue']);

        $this->defaults = [
            'html_custom_styles' => \File::get(base_path().'/themes/bolo/assets/css/editor_back.css'),
            'html_style_image' =>
                array (
                    array (
                        'class_label' => 'Rounded',
                        'class_name' => 'oc-img-rounded',
                    ),
                    array (
                        'class_label' => 'Bordered',
                        'class_name' => 'oc-img-bordered',
                    ),
                    array(
                        'class_label' => 'Public Only',
                        'class_name' => 'public-only',
                    )
                ),
            'html_style_link' =>
                array (
                    array (
                        'class_label' => 'Green',
                        'class_name' => 'oc-link-green',
                    ),
                    array (
                        'class_label' => 'Strong',
                        'class_name' => 'oc-link-strong',
                    ),
                    array(
                        'class_label' => 'CTA',
                        'class_name' => 'btn-cta',
                    ),
                    array(
                        'class_label' => 'CTA Small',
                        'class_name' => 'btn-cta-small',
                    ),
                    array(
                        'class_label' => 'Public Only',
                        'class_name' => 'public-only',
                    )
                ),
            'html_style_paragraph' =>
                array (
                    array (
                        'class_label' => 'Bordered',
                        'class_name' => 'oc-text-bordered',
                    ),
                    array (
                        'class_label' => 'Gray',
                        'class_name' => 'oc-text-gray',
                    ),
                    array (
                        'class_label' => 'Spaced',
                        'class_name' => 'oc-text-spaced',
                    ),
                    array (
                        'class_label' => 'Uppercase',
                        'class_name' => 'oc-text-uppercase',
                    ),
                    array(
                        'class_label' => 'Public Only',
                        'class_name' => 'public-only',
                    ),
                    array(
                        'class_label' => 'Gallery',
                        'class_name' => 'gallery',
                    ),
                ),
            'html_style_table' =>
                array (
                    array (
                        'class_label' => 'Dashed Borders',
                        'class_name' => 'oc-table-dashed-borders',
                    ),
                    array (
                        'class_label' => 'Alternate Rows',
                        'class_name' => 'oc-table-alternate-rows',
                    ),
                ),
            'html_style_table_cell' =>
                array (
                    array (
                        'class_label' => 'Highlighted',
                        'class_name' => 'oc-cell-highlighted',
                    ),
                    array (
                        'class_label' => 'Thick Border',
                        'class_name' => 'oc-cell-thick-border',
                    ),
                ),
        ];
    }

    public function getSettingsValue($name){
        if(isset($this->defaults[$name]))
            return $this->defaults[$name];
    }

}

