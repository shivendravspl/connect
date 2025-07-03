<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function index()
    {
        $menuList = Menu::select('*')->orderBy('menu_name', 'asc')->get();
        return view('menu_builder.menu_builder', compact('menuList'));
    }

    public function setPosition()
    {
        $this->_menus = array();
        $this->_sort = 0;
        $this->_sort;
        $this->parseMenu($_POST['menu']);

        $status = 0;

        foreach ($this->_menus as $row) {
            $result = Menu::where('id', $row['id'])
                ->update(['menu_position' => $row['menu_position'], 'parent_id' => $row['parent_id']]);
            if ($result) {
                $status = 1;
            }
        }

        if ($status) {
            $response = array('status' => 1);
        } else {
            $response = array('status' => 0);
        }

        return Response()->json($response);
    }

    private function parseMenu($menus, $parent_id = 0)
    {
        foreach ($menus as $menu) {
            $this->_sort++;
            $this->_menus[] = array(
                'id' => $menu['id'],
                'menu_position' => $this->_sort,
                'parent_id' => $parent_id
            );

            if (isset($menu['children'])) {
                $this->parseMenu($menu['children'], $menu['id']);
            }
        }
    }

    public function getParentMenus(Request $request)
    {

        $sub_menu_list = $request->id ? implode(',', $this->getSubMenu($request->id, [$request->id])) : '0';
        $menu_list = Menu::where('status', 'A')
            ->whereNotIn('id', explode(',', $sub_menu_list))
            ->orderBy('menu_name')
            ->select('id', 'menu_name')
            ->get();

        $status = $menu_list->isNotEmpty() ? 200 : 400;

        return response()->json(['result' => $menu_list, 'status' => $status]);
    }

    function getSubMenu($parent_id, $sub_menu_list = [])
    {
        // Parameterized query to prevent SQL injection
        $query = "SELECT m.id, m.menu_name, m.menu_url, COALESCE(tbl.count, 0) AS count
        FROM menus m
        LEFT OUTER JOIN (
            SELECT parent_id, COUNT(*) AS count
            FROM menus
            GROUP BY parent_id
        ) tbl ON m.id = tbl.parent_id
        WHERE m.parent_id = :parent_id
        ORDER BY menu_position ASC
    ";

        // Execute the query with the parent_id parameter
        $result = DB::select($query, ['parent_id' => $parent_id]);

        // Convert stdClass objects to arrays
        $result = array_map(fn($value) => (array)$value, $result);

        // Process the result and build the submenu list
        foreach ($result as $row) {
            $sub_menu_list[] = $row['id'];
            if ($row['count'] > 0) {
                $sub_menu_list = $this->getSubMenu($row['id'], $sub_menu_list);
            }
        }

        return $sub_menu_list;
    }


    public function show_menu(Request $request)
    {
        $result = Menu::find($request->id);
        return Response()->json($result);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'menu_name' => 'required|min:2',
            ], [
                'menu_name.required' => 'Menu Name is required!',
                'menu_name.min' => 'Menu Name must be at least 2 characters!',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $message = $errors->count() > 1 ? $errors->all('<li>:message</li>') : $errors->first();
                return response()->json(['status' => 400, 'message' => $message]);
            }

            $menuInsertData = $request->id ? Menu::findOrFail($request->id) : new Menu();

            $menuInsertData->fill([
                'menu_name' => ucwords($request->menu_name),
                'parent_id' => $request->parent_id ?? null,
                'menu_icon' => $request->menu_icon,
                'menu_position' => $request->menu_position,
                'menu_url' => $request->menu_url,
                'status' => $request->menu_status,
            ]);

            if ($request->id) {
                $menuInsertData->updated_at = now();
            }

            $result = $menuInsertData->save();
            $action = $request->id ? 'updated' : 'added';
            $status = $result ? 200 : 400;
            $message = $result ? "Menu $action successfully!" : "Menu not $action!";

            return response()->json(['status' => $status, 'message' => $message]);
        }
    }

}
