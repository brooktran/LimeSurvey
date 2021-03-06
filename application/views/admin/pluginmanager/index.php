<?php

/**
* Index view for plugin manager
*
* @since 2015-10-02
* @author Olle Haerstedt
*/

?>
<?php $pageSize = intval(Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize'])); ?>

<h3 class="pagetitle"><?php eT('Plugin manager'); ?></h3>
<div style="width: 75%; margin: auto;">
    <?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    $sort = new CSort();
    $sort->attributes = array(
        'name'=>array(
            'asc'=> 'name',
            'desc'=> 'name desc',
        ),
        'description'=>array(
            'asc'=> 'description',
            'desc'=> 'description desc',
        ),
        'status'=>array(
            'asc'=> 'active',
            'desc'=> 'active desc',
            'default'=> 'desc',
        ),
    );
    $sort->defaultOrder = array(
        'name'=>CSort::SORT_ASC,
    );

    $providerOptions = array(
        'pagination'=>array(
            'pageSize'=>$pageSize,
        ),
        'sort'=>$sort,
        'caseSensitiveSort'=> false,
    );

    $dataProvider = new CArrayDataProvider($data, $providerOptions);

    $gridColumns = array(
        array(// display the status
            'header' => gT('Status'),
            'type' => 'html',
            'name' => 'status',
            //'value' => function($data) { return ($data['active'] == 1 ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Active'), array('width' => 32, 'height' => 32)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Inactive'), array('width' => 32, 'height' => 32))); }
            'value' => function($data)
            {
                if ($data['active'] == 1)
                {
                    return "<span class='fa fa-circle'></span>";
                }
                else
                {
                    return "<span class='fa fa-circle-thin'></span>";
                }
            }
        ),
        array(// display the 'name' attribute
            'header' => gT('Plugin'),
            'name' => 'name'
        ),
        array(// display the 'description' attribute
            'header' => gT('Description'),
            'name' => 'description'
        ),
        array(// display the activation link
            'type' => 'html',
            'header' => gT('Action'),
            'name' => 'action',
            'htmlOptions' => array(
                'style' => 'white-space: nowrap;'
            ),
            'value' => function($data) {

                $output='';
                if(Permission::model()->hasGlobalPermission('settings','update'))
                {
                    if ($data['active'] == 0)
                    {
                        $output = "<a href='" . Yii::app()->createUrl('/admin/pluginmanager/sa/activate', array('id' => $data['id'])) . "' class='btn btn-default btn-xs btntooltip'><span class='fa fa-power-off'>&nbsp;</span>".gT('Activate')."</a>";
                    } else {
                        $output = "<a href='" . Yii::app()->createUrl('/admin/pluginmanager/sa/deactivate', array('id' => $data['id'])) . "'class='btn btn-warning btn-xs'><span class='fa fa-power-off'>&nbsp;</span>".gT('Deactivate')."</a>";
                    }
                }
                if(count($data['settings'])>0)
                {
                    $output .= "&nbsp;<a href='" . Yii::app()->createUrl('/admin/pluginmanager/sa/configure', array('id' => $data['id'])) . "' class='btn btn-default btn-xs'><span class='icon-edit'>&nbsp;</span>" . gT('Configure') . "</a>";
                }

                return $output;
            }
        ),
    );

    /*
    array(            // display a column with "view", "update" and "delete" buttons
    'class' => 'CallbackColumn',
    'label' => function($data) { return ($data->active == 1) ? "deactivate": "activate"; },
    'url' => function($data) { return array("/plugins/activate", "id"=>$data["id"]); }
    )
    );
    */

    $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider'=>$dataProvider,
        'id' => 'plugins-grid',
        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).') .' '.sprintf(gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $pageSize,
                Yii::app()->params['pageSizeOptions'],
                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
        'columns'=>$gridColumns,
    ));
    ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function() {
            $.fn.yiiGridView.update('plugins-grid',{ data:{ pageSize: $(this).val() }});
        });
    });
</script>
