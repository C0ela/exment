<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
//use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Widgets\Box;
//use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Exceedone\Exment\Model\Dashboard;
use Exceedone\Exment\Model\DashboardBox;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomView;
use Exceedone\Exment\Enums\DashboardBoxType;
use Exceedone\Exment\Enums\ViewKindType;

class DashboardBoxController extends AdminControllerBase
{
    use HasResourceActions;
    protected $dashboard;
    protected $dashboard_box_type;
    protected $row_no;
    protected $column_no;

    public function __construct(Request $request)
    {
        $this->setPageInfo(exmtrans("dashboard.header"), exmtrans("dashboard.header"));
    }

    public function index(Request $request, Content $content)
    {
        return redirect(admin_url(''));
    }
    
    /**
     * Delete interface.
     *
     * @return Content
     */
    public function delete(Request $request, $suuid)
    {
        // get suuid
        $box = DashBoardBox::findBySuuid($suuid);
        if (isset($box)) {
            $box->delete();
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
    
    /**
     * get box html from ajax
     */
    public function getHtml($suuid)
    {
        // get dashboardbox object
        $box = DashBoardBox::findBySuuid($suuid);
        
        // get box html --------------------------------------------------
        if (isset($box)) {
            $dashboard_box_item = $box->dashboard_box_item;
            $header = $dashboard_box_item->header();
            $body = $dashboard_box_item->body();
            $footer = $dashboard_box_item->footer();
        }

        // get dashboard box
        return [
            'header' => $header ?? null,
            'body' => $body ?? null,
            'footer' => $footer ?? null,
            'suuid' => $suuid,
        ];
    }
    
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $form = new Form(new DashboardBox);
        // set info with query --------------------------------------------------
        // get request
        $request = request();
        // get dashboard, row_no, column_no, ... from query "dashboard_suuid"
        list($dashboard, $dashboard_box_type, $row_no, $column_no) = $this->getDashboardInfo($id);
        if (!isset($dashboard)) {
            return redirect(admin_url(''));
        }

        $form->display('dashboard_view_name', exmtrans('dashboard.dashboard_view_name'))->default($dashboard->dashboard_view_name);
        $form->hidden('dashboard_id')->default($dashboard->id);

        $form->display('row_no', exmtrans('dashboard.row_no'))->default($row_no);
        $form->hidden('row_no')->default($row_no);

        $form->display('column_no', exmtrans('dashboard.column_no'))->default($column_no);
        $form->hidden('column_no')->default($column_no);

        $form->display('dashboard_box_type_display', exmtrans('dashboard.dashboard_box_type'))->default(exmtrans("dashboard.dashboard_box_type_options.$dashboard_box_type"));
        $form->hidden('dashboard_box_type')->default($dashboard_box_type);

        $form->text('dashboard_box_view_name', exmtrans("dashboard.dashboard_box_view_name"))->rules("max:40")->required();
        
        // Option Setting --------------------------------------------------
        $form->embeds('options', function ($form) use ($dashboard_box_type) {
            $classname = DashboardBoxType::getEnum($dashboard_box_type)->getDashboardBoxItemClass();
            $classname::setAdminOptions($form);
        })->disableHeader();
        
        $form->tools(function (Form\Tools $tools) use ($id, $form) {
            $tools->disableList();

            // addhome button
            $tools->append('<a href="'.admin_url('').'" class="btn btn-sm btn-default"  style="margin-right: 5px"><i class="fa fa-home"></i>&nbsp;'. exmtrans('common.home').'</a>');
        });
        // add form saving and saved event
        $this->manageFormSaving($form);
        return $form;
    }

    protected function manageFormSaving($form)
    {
        // before saving
        $form->saving(function ($form) {
            $classname = DashboardBoxType::getEnum($form->dashboard_box_type)->getDashboardBoxItemClass();
            $classname::saving($form);
        });

        // saved. redirect to top
        $form->saved(function ($form) {
            admin_toastr(trans('admin.save_succeeded'));

            return redirect(admin_url());
        });
    }

    /**
     * get dashboard info using id, or query
     */
    protected function getDashboardInfo($id)
    {
        // set info with query --------------------------------------------------
        // get request
        $request = request();
        // get dashboard_id from query "dashboard_suuid"
        if (isset($id)) {
            $dashboard_box = DashboardBox::getEloquent($id);
            $dashboard = $dashboard_box->dashboard;
            return [$dashboard, $dashboard_box->dashboard_box_type, $dashboard_box->row_no, $dashboard_box->column_no];
        }
            
        if (!is_null($request->input('dashboard_id'))) {
            $dashboard = Dashboard::getEloquent($request->input('dashboard_id'));
        } else {
            // get dashboard_suuid from query
            $dashboard_suuid = $request->query('dashboard_suuid');
            if (!isset($dashboard_suuid)) {
                return [null, null, null, null];
            }
            $dashboard = Dashboard::findBySuuid($dashboard_suuid) ?? null;
        }
        if (!isset($dashboard)) {
            return [null, null, null, null];
        }

        if (!is_null($request->input('dashboard_box_type'))) {
            $dashboard_box_type = $request->input('dashboard_box_type');
        } else {
            // get dashboard_box_type from query
            $dashboard_box_type = $request->query('dashboard_box_type');
        }

        // row_no
        if (!is_null($request->input('row_no'))) {
            $row_no = $request->input('row_no');
        } else {
            // get from query
            $row_no = $request->query('row_no');
        }

        // column_no
        if (!is_null($request->input('column_no'))) {
            $column_no = $request->input('column_no');
        } else {
            // get from query
            $column_no = $request->query('column_no');
        }
        return [$dashboard, $dashboard_box_type, $row_no, $column_no];
    }
    
    /**
     * get views using table id
     * @param mixed custon_table id
     */
    public function tableViews(Request $request, $dashboard_type)
    {
        $id = $request->get('q');
        if (!isset($id)) {
            return [];
        }
        // get custom views
        $custom_table = CustomTable::getEloquent($id);
        $views = $custom_table->custom_views
            ->filter(function ($value) use ($dashboard_type) {
                if ($dashboard_type == DashboardBoxType::CALENDAR) {
                    return array_get($value, 'view_kind_type') == ViewKindType::CALENDAR;
                } elseif ($dashboard_type == DashboardBoxType::CHART) {
                    return array_get($value, 'view_kind_type') == ViewKindType::AGGREGATE;
                } else {
                    return array_get($value, 'view_kind_type') != ViewKindType::CALENDAR;
                }
            })->map(function ($value) {
                return array('id' => $value->id, 'text' => $value->view_view_name);
            });
        // if count > 0, return value.
        if (!is_null($views) && count($views) > 0) {
            return $views;
        }

        // create default view
        $view = createDefaultView($custom_table);
        createDefaultViewColumns($view);
        return [['id' => $view->id, 'text' => $view->view_view_name]];
    }
    
    /**
     * get view columns using view id
     * @param mixed custon_view id
     */
    public function chartAxis(Request $request, $axis_type)
    {
        $id = $request->get('q');
        if (!isset($id)) {
            return [];
        }
        // get custom views
        $custom_view = CustomView::getEloquent($id);

        return $custom_view->getColumnsSelectOptions($axis_type == 'y');
    }
}
