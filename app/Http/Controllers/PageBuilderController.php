<?php

namespace App\Http\Controllers;

use App\Models\FormBuilder;
use App\Models\Menu;
use App\Models\PageBuilder;
use App\Models\User;
use App\Rules\ReservedKeyword;
use App\Traits\DatabaseTrait;
use App\Traits\FileBackupTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\PermissionService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PageBuilderController extends Controller
{
    use DatabaseTrait;
    use FileBackupTrait;

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $page_list = PageBuilder::startSearch(Request()->query("page_search"))->orderByDesc("id")->paginate(10);

        return view('page_builder.index', compact('page_list'));
    }

    public function create()
    {
        return view('page_builder.page_builder');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'page_name' => ['required', new ReservedKeyword],
        ]);


        $data['upper_case'] = Str::upper($request->page_name);
        $data['lower_case'] = Str::lower($request->page_name);
        $data['snake_case'] = Str::snake($request->page_name);
        $data['studly_case'] = Str::studly($request->page_name);
        $studly_case = Str::studly($request->page_name);

        PageBuilder::create($data);

        //================Make Resource Controller===================
        $controllerDirectory = app_path("Http/Controllers/{$studly_case}");
        $controllerPath = "{$controllerDirectory}/{$studly_case}Controller.php";

        if (!File::exists($controllerDirectory)) {
            File::makeDirectory($controllerDirectory, 0755, true);
        }

        $stubPath = base_path('stubs/controller.stub');
        $stubContent = File::get($stubPath);

        $controllerContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ view_directory }}', '{{ view_name }}'],
            ["App\Http\Controllers\\{$studly_case}", "{$studly_case}Controller", $data['snake_case'], $data['snake_case']],
            $stubContent
        );

        File::put($controllerPath, $controllerContent);

        //================Create View===================
        $viewDirectory = resource_path("views/{$data['snake_case']}");
        $viewPath = "{$viewDirectory}/{$data['snake_case']}_list.blade.php";

        if (!File::exists($viewDirectory)) {
            File::makeDirectory($viewDirectory, 0755, true);
        }

        if (!File::exists($viewPath)) {
            // Use the view stub template
            $viewStub = base_path('stubs/view.stub');
            $viewContent = File::get($viewStub);

            $viewContent = str_replace(
                ['{{ $page_name }}', '{{ $snake_case }}'],
                [$request->page_name, $data['snake_case']],
                $viewContent
            );

            File::put($viewPath, $viewContent);
        }
        //================Register Routes===================
        $routePath = base_path('routes/web.php');
        $routeContent = "\n\nRoute::resource('{$data['snake_case']}', \App\Http\Controllers\\{$studly_case}\\{$studly_case}Controller::class);";

        File::append($routePath, $routeContent);

        //===================Create Entry in Menu Model ==================
        $menu = new Menu();
        $menu->menu_name = $request->page_name;
        $menu->menu_url = "{$data['snake_case']}";
        $menu->parent_id = 0;
        $menu->menu_position = 1;
        $menu->permissions = 'list-' . $studly_case;
        $menu->status = 'A';
        $menu->save();
        return redirect(route("page-builder.index"))->with("toast_success", 'Page created successfully');
    }

    public function edit(PageBuilder $pageBuilder)
    {
        $data = $pageBuilder;
        return view('page_builder.page_builder', compact('data'));
    }

    public function update(Request $request, PageBuilder $pageBuilder)
    {
        $data = $request->validate([
            'page_name' => ['required'],
        ]);
        $data['upper_case'] = Str::upper($request->page_name);
        $data['lower_case'] = Str::lower($request->page_name);
        $data['snake_case'] = Str::snake($request->page_name);
        $pageBuilder->update($data);

        return redirect(route("page-builder.index"))->with("toast_success", 'Page updated successfully');
    }

    public function destroy(PageBuilder $pageBuilder)
    {
        try {
            $snake_case = $pageBuilder->snake_case;
            $studly_case = Str::studly($snake_case);
            $permissionArray = ["list-" . $studly_case, 'add-' . $studly_case, 'edit-' . $studly_case, 'delete-' . $studly_case];
            $lower_case = $pageBuilder->lower_case;

            // Delete the page first
            $pageBuilder->delete();

            // Delete controller file
            $controllerPath = app_path("Http/Controllers/{$studly_case}/{$studly_case}Controller.php");
            if (File::exists($controllerPath)) {
                File::delete($controllerPath);
                @rmdir(app_path("Http/Controllers/{$studly_case}"));
            }

            // Delete model file
            $modelPath = app_path("Models/{$studly_case}/{$studly_case}.php");
            if (File::exists($modelPath)) {
                File::delete($modelPath);
                @rmdir(app_path("Models/{$studly_case}"));
            }

            // Delete view directory
            $viewDirectory = resource_path("views/{$snake_case}");
            if (File::exists($viewDirectory)) {
                File::deleteDirectory($viewDirectory);
            }

            // Remove route entry
            $routeFilePath = base_path('routes/web.php');
            $routeContent = "\n\nRoute::resource('{$snake_case}', \App\Http\Controllers\\{$studly_case}\\{$studly_case}Controller::class);";
            file_put_contents($routeFilePath, str_replace($routeContent, '', file_get_contents($routeFilePath)));

            // Drop table if exists
            if (Schema::hasTable($lower_case)) {
                Schema::drop($lower_case);
            }

            // Clean up related data
            DB::table('menus')->where('menu_url', $snake_case)->delete();
            FormBuilder::where('page_name', $studly_case)->delete();

            // Remove permissions safely
            $this->deletePermissions($permissionArray);

            return redirect(route("page-builder.index"))->with("toast_success", 'Page deleted successfully');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Page deletion error: ' . $e->getMessage());

            // Return with a generic success message even if some cleanup failed
            return redirect(route("page-builder.index"))->with("toast_success", 'Page deleted successfully (some cleanup may not have completed)');
        }
    }

    protected function deletePermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            try {
                $permission = Permission::findByName($permission);
                if ($permission) {
                    $permission->delete();
                }
            } catch (PermissionDoesNotExist $e) {
                // Permission doesn't exist - we can ignore this
                continue;
            }
        }
    }

    public function formGenerate(Request $request)
    {
        $page_id = base64_decode($request->page);
        $page = PageBuilder::where('id', $page_id)->first()->toArray();
        $forms_element = FormBuilder::where('page_id', $page['id'])->orderBy('sorting_order', 'asc')->get();
        $source_table = PageBuilder::where('id', '!=', $page_id)->pluck('page_name', 'id');
        return view('page_builder.page_builder_form', compact('page', 'forms_element', 'source_table'));
    }

    public function addFormElement(Request $request)
    {
        $sorting_id = FormBuilder::where('page_id', $request->page_id)->max('sorting_order') + 1;
        $data['page_id'] = $request->page_id;
        $data['page_name'] = $request->page_name;
        $data['input_type'] = $request->type;
        $data['column_name'] = $request->name;
        $data['column_title'] = $request->placeholder;
        $data['column_width'] = $request->column_width;
        $data['placeholder'] = $request->placeholder;
        $data['is_required'] = $request->is_required;
        $data['sorting_order'] = $sorting_id;
        FormBuilder::create($data);
        return response()->json(array('status' => 200), 200);
    }

    public function getFormElementDetails(Request $request)
    {
        $form_id = $request->form_id;
        $data = FormBuilder::find($form_id);
        return response()->json(array('status' => 200, 'data' => $data), 200);
    }

    public function updateFormElement(Request $request)
    {

        $data = [
            'column_title' => $request->label_name,
            'column_name' => $request->column_name,
            'column_width' => $request->width,
            'placeholder' => $request->placeholder ?? null,
            'default_value' => $request->default_value ?? null,
            'is_required' => $request->is_required ?? 'N',
            'is_unique' => $request->is_unique ?? 'N',
            'is_nullable' => $request->is_nullable ?? 'N',
            'is_switch' => $request->is_switch ?? 'N',
            'source_table' => $request->source_table ?? null,
            'source_table_column_key' => $request->source_table_key ?? null,
            'source_table_column_value' => $request->source_table_value ?? null,
            'column_type' => $request->column_type ?? null,
            'description' => $request->description ?? null,
            'column_length' => $request->column_length ?? null,
            'min_value' => $request->min_value ?? null,
            'max_value' => $request->max_value ?? null,
        ];

        $query = FormBuilder::find($request->form_id);
        if ($query) {
            $query->update($data);
            return response()->json([
                'status' => 200,
                'message' => 'Form updated successfully',
                'form_id' => $request->form_id,
                'new_width' => $data['column_width'],
                'column_title' => $data['column_title'],
                'column_name' => $data['column_name'],
                'placeholder' => $data['placeholder'],
                'description' => $data['description']
            ]);
        }

        return response()->json(['status' => 400, 'message' => 'Form element not found']);
    }

    public function generateForm(Request $request)
    {
        $request->validate([
            'page_id' => 'required|integer',
            'page_name' => 'required|string',
        ]);

        $page_id = $request->page_id;
        $page_name = $request->page_name;

        try {
            $fillable_property = FormBuilder::where('page_id', $page_id)->pluck('column_name')->toArray();
            $studly_case = Str::studly($page_name);
            $modelDirectory = app_path("Models/{$studly_case}");
            $modelPath = "{$modelDirectory}/{$studly_case}.php";
            $table_name = Str::snake($page_name);

            // Backup existing files
            $this->backupExistingFiles($studly_case);

            //=================Create Table================
            $get_form_data = FormBuilder::where('page_id', $page_id)->orderBy('sorting_order', 'asc')->get();
            // Initialize an array to hold the formatted data
            $formattedTableData = [];
            // Loop through each entry in the tableData
            foreach ($get_form_data as $row) {
                // Check if the table is already added to the formatted data
                if (!isset($formattedTableData[$row->page_name])) {
                    $formattedTableData[$row->page_name] = (object)[
                        'table_name' => Str::snake($row->page_name),
                        'table_columns' => []
                    ];
                }

                // Add the column data to the respective table entry
                $formattedTableData[$row->page_name]->table_columns[] = (object)[
                    'column_type' => 'string',
                    'column_name' => $row->column_name,
                    'column_length' => '255',
                    'is_nullable' => $row->is_nullable == 'Y' ? 1 : 0,
                    'is_unique' => $row->is_unique == 'Y' ? 1 : 0,
                    'is_unsigned' => 0,
                    'column_default' => $row->default_value,
                ];
            }
            // Convert associative array to indexed array for return
            $tableData = array_values($formattedTableData);
            if (count($tableData) > 0) {
                $this->setupDatabase($tableData);
            }

            if (!File::exists($modelDirectory)) {
                File::makeDirectory($modelDirectory, 0755, true);
            }

            $this->generateModel($modelPath, $studly_case, $fillable_property, $table_name, $page_id);
            $this->generateController($page_id, $studly_case, $table_name);
            $this->generateListPage($page_id, $page_name, $table_name, $fillable_property);
            $this->generateFormPage($page_id, $page_name, $table_name);

            $permissions = [
                "add-{$studly_case}",
                "edit-{$studly_case}",
                "list-{$studly_case}",
                "delete-{$studly_case}",

            ];
            foreach ($permissions as $permission) {
                $this->permissionService->createAndAssignPermission($permission, $studly_case);
            }
            return response()->json(['status' => 200], 200);
        } catch (\Exception $exception) {

            Log::error('Error generating form: ' . $exception->getMessage());
            return response()->json(['status' => 400, 'error' => $exception->getMessage()], 400);
        }
    }

    private function generateModel($modelPath, $studly_case, $fillable_property, $table_name, $page_id)
    {

        $stubPath = base_path('stubs/model.stub');
        if (!File::exists($stubPath)) {
            throw new \Exception("Model stub not found.");
        }

        $stubContent = File::get($stubPath);
        $fillable_property_string = implode("', '", $fillable_property);
        $fillable_property_code = "\n    protected \$fillable = ['{$fillable_property_string}'];\n";
        $table_name_code = "\n    protected \$table = '{$table_name}';\n";
        $softDeletes_code = "\n    use SoftDeletes;\n";
        $form_details = FormBuilder::where('page_id', $page_id)
            ->where(function ($query) {
                $query->where('input_type', 'image_upload')
                    ->orWhere('input_type', 'file_upload');
            })
            ->select('column_name', 'input_type')->get()->toArray();

        $info = '';

        if (count($form_details) > 0) {
            $info .= "public function fileInfo(\$key=false)\n";
            $info .= "{\n";
            $info .= "    \$file_info = [\n";

            foreach ($form_details as $form_detail) {
                $column_name = $form_detail['column_name'];
                $input_type = $form_detail['input_type'];

                $info .= "        '{$column_name}' => [\n";
                $info .= "            'disk' => config('admin.settings.upload_disk'),\n";

                if ($input_type == 'image_upload') {
                    $info .= "            'quality' => config('admin.images.image_quality'),\n";
                    $info .= "            'webp' => ['action' => 'none', 'quality' => config('admin.images.image_quality')],\n";
                    $info .= "            'original' => ['action' => 'resize', 'width' => 1920, 'height' => 1080, 'folder' => '/upload/'],\n";
                } else {
                    $info .= "            'original' => ['folder' => '/upload/'],\n";
                }

                $info .= "        ],\n";
            }

            $info .= "    ];\n";
            $info .= "    return (\$key) ? \$file_info[\$key] : \$file_info;\n";
            $info .= "}\n";
        }

        $methods = '';

        foreach ($form_details as $form_detail) {
            $column_name = $form_detail['column_name'];
            $camel_case_name = Str::ucfirst(Str::camel($column_name));

            if ($form_detail['input_type'] == 'image_upload') {
                $methods .= <<<EOD
                        public function set{$camel_case_name}Attribute()
                        {
                            if (request()->hasFile('{$column_name}')) {
                                \$this->attributes['{$column_name}'] = \$this->akImageUpload(request()->file("{$column_name}"), \$this->fileInfo("{$column_name}"), \$this->getOriginal('{$column_name}'));
                            }
                        }

                        public function get{$camel_case_name}Attribute(\$value)
                        {
                            if (\$value && \$this->akFileExists(\$this->fileInfo("{$column_name}")['disk'], \$this->fileInfo("{$column_name}")['original']["folder"], \$value)) {
                              return asset('upload/' . \$value);
                            }
                            return false;
                        }

                        public function setAk{$camel_case_name}DeleteAttribute(\$delete)
                        {
                            if (!request()->hasFile('{$column_name}') && \$delete == 1) {
                                \$this->attributes['{$column_name}'] = \$this->akImageUpload('', \$this->fileInfo("{$column_name}"), \$this->getOriginal('{$column_name}'), 1);
                            }
                        }

                    EOD;
            } else {
                $methods .= <<<EOD
                            public function set{$camel_case_name}Attribute()
                            {
                                if (request()->hasFile('{$column_name}')) {
                                    \$this->attributes['{$column_name}'] = \$this->akFileUpload(request()->file("{$column_name}"), \$this->fileInfo("{$column_name}"), \$this->getOriginal('{$column_name}'));
                                }
                            }

                            public function get{$camel_case_name}Attribute(\$value)
                            {
                                if (\$value && \$this->akFileExists(\$this->fileInfo("{$column_name}")['disk'], \$this->fileInfo("{$column_name}")['original']["folder"], \$value)) {
                                    return asset('upload/' . \$value);
                                }
                                return false;
                            }

                            public function setAk{$camel_case_name}DeleteAttribute(\$delete)
                            {
                                if (!request()->hasFile('{$column_name}') && \$delete == 1) {
                                    \$this->attributes['{$column_name}'] = \$this->akFileUpload('', \$this->fileInfo("{$column_name}"), \$this->getOriginal('{$column_name}'), 1);
                                }
                            }

                            EOD;
            }
        }

        $relationDetails = FormBuilder::where('page_id', $page_id)
            ->whereNotNull('source_table')
            ->whereNotNull('source_table_column_key')
            ->select('column_name', 'source_table')
            ->get()
            ->toArray();
        foreach ($relationDetails as $relationDetail) {
            $relation_table_name = $this->get_studly_case($relationDetail['source_table']);
            $lower_case = Str::lower($relation_table_name);

            $column_name = $relationDetail['column_name'];

            $methods .= 'public function ' . $lower_case . '() {' . PHP_EOL;
            $methods .= '    return $this->belongsTo("App\Models\\' . $relation_table_name . '\\' . $relation_table_name . '", "' . $column_name . '");' . PHP_EOL;
            $methods .= '}' . PHP_EOL . PHP_EOL;
        }
        $modelContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ fillable }}', '{{ table }}', '{{ softDeletes }}', '{{ table_name }}', '{{ file_info }}', '{{ methods }}'],
            ["App\Models\\{$studly_case}", "{$studly_case}", $fillable_property_code, $table_name_code, $softDeletes_code, $table_name, $info, $methods],
            $stubContent
        );

        File::put($modelPath, $modelContent);
    }

    private function generateListPage($page_id, $page_name, $table_name, $fillable_property)
    {
        // Define paths and stub content
        $viewDirectory = resource_path("views/{$table_name}");
        $viewPath = "{$viewDirectory}/{$table_name}_list.blade.php";
        $viewStub = base_path('stubs/updated_view.stub');
        $viewContent = File::get($viewStub);

        // Filter out unwanted columns
        $filteredColumns = array_filter($fillable_property, function ($col) {
            return !in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']);
        });

        // Quote column names
        $quotedColumns = array_map(function ($col) {
            return "'$col'";
        }, $filteredColumns);

        // Prepare column names and related column titles
        $columnNames = implode(', ', $quotedColumns);
        $formDetails = FormBuilder::where('page_id', $page_id)->orderBy('sorting_order')->pluck('column_title')->toArray();
        $column_name_field = '';
        foreach ($formDetails as $col) {
            $column_name_field .= "   <th>{$col}</th>";
        }

        // Prepare table fields
        $table_columns = FormBuilder::where('page_id', $page_id)->orderBy('sorting_order')->get();
        $table_field = '';

        foreach ($table_columns as $cols) {
            if ($cols->source_table != null && $cols->source_table_column_value != null) {
                $relation_table = Str::lower($this->get_studly_case($cols->source_table));
                $table_field .= '<td>{{ $data->' . $relation_table . '->' . $cols->source_table_column_value . ' ?? "" }}</td>' . "\n";
            } else {
                switch ($cols->input_type) {
                    case 'image_upload':
                        $table_field .= '<td class="image-col">' . "\n";
                        $table_field .= '@if ($data->' . $cols->column_name . ')' . "\n";
                        $table_field .= '<a href="{{ $data->' . $cols->column_name . ' }}" target="_blank" class="item-image lightbox">' . "\n";
                        $table_field .= '<div style="background-image: url(\'{{ $data->' . $cols->column_name . ' }}\')"></div>' . "\n";
                        $table_field .= '</a>' . "\n";
                        $table_field .= '@endif' . "\n";
                        $table_field .= '</td>' . "\n";
                        break;
                    case 'file_upload':
                        $table_field .= '<td>' . "\n";
                        $table_field .= '@if ($data->' . $cols->column_name . ')' . "\n";
                        $table_field .= '<a href="{{ $data->' . $cols->column_name . ' }}" target="_blank">' . "\n";
                        $table_field .= 'View' . "\n";
                        $table_field .= '</a>' . "\n";
                        $table_field .= '@endif' . "\n";
                        $table_field .= '</td>' . "\n";
                        break;
                    case 'radio':
                    // Handle radio button display in list view
                    if ($cols->source_table && $cols->source_table_column_value) {
                        $relation_table = Str::lower($this->get_studly_case($cols->source_table));
                        $table_field .= '<td>{{ optional($data->' . $relation_table . ')->' . $cols->source_table_column_value . ' ?? "" }}</td>' . "\n";
                    } else {
                        // Fallback display for radio buttons without source table
                        $table_field .= '<td>{{ $data->' . $cols->column_name . ' }}</td>' . "\n";
                    }
                    break;
                    default:
                        $table_field .= '<td>{{ $data->' . $cols->column_name . ' }}</td>' . "\n";
                        break;
                }
            }
        }

        $studly_case_page_name = Str::studly($page_name);
        // Replace placeholders in the stub content
        $viewContent = str_replace(
            ['{{ $page_name }}', '{{ $snake_case }}', '{{ $columns }}', '{{ column_names }}', '{{ table_field }}', '{{ studly_case }}'],
            [$page_name, $table_name, $columnNames, $column_name_field, $table_field, $studly_case_page_name],
            $viewContent
        );

        // Create directory if not exists
        if (!File::exists($viewDirectory)) {
            File::makeDirectory($viewDirectory, 0755, true);
        }

        // Save the generated view content to the file
        File::put($viewPath, $viewContent);
    }


    private function generateFormPage($page_id, $page_name, $table_name)
    {
        $viewDirectory = resource_path("views/{$table_name}");
        $viewPath = "{$viewDirectory}/{$table_name}_form.blade.php";
        $viewStub = base_path('stubs/form.stub');
        $viewContent = File::get($viewStub);
        $fields = $this->generateFields($page_id);
        $studly_case_page_name = Str::studly($page_name);
        $viewContent = str_replace(
            ['{{ $page_name }}', '{{ $snake_case }}', '{{ $fields }}', '{{ studly_case }}'],
            [$page_name, $table_name, $fields, $studly_case_page_name],
            $viewContent
        );

        File::put($viewPath, $viewContent);
    }

    private function generateFields($pageId)
    {
        $formElements = FormBuilder::where('page_id', $pageId)->orderBy('sorting_order')->get();
        $fields = '';

        foreach ($formElements as $formElement) {

            $fields .= $this->generateFieldHtml($formElement);
        }

        return $fields;
    }

    private function generateFieldHtml($formElement)
    {
        // Extracting the form element properties
        $inputType = $formElement->input_type;
        $columnWidth = $formElement->column_width;
        $columnName = $formElement->column_name;
        $columnTitle = $formElement->column_title;
        $placeholder = $formElement->placeholder ?? '';
        $isRequired = $formElement->is_required;
        $sourceTable = $formElement->source_table;
        $sourceTableColumnKey = $formElement->source_table_column_key;
        $sourceTableColumnValue = $formElement->source_table_column_value;
        $description = $formElement->description ?? '';
        $columnType = $formElement->column_type;
        $isSwitch = $formElement->is_switch;
        $minValue = $formElement->min_value;
        $maxValue = $formElement->max_value;

        // Required field indicator
        $isRequired = strtoupper($formElement->is_required) === 'Y';
        $requiredIndicator = $isRequired ? '<span class="text-danger">*</span>' : '';
        $requiredClass = $isRequired ? 'required' : '';
        $requiredAttr = $isRequired ? 'required' : '';

        // Helper function to generate common input HTML structure
        $generateInputHtml = function ($type, $extraClasses = '', $extraAttrs = '')
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $placeholder,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $description,
            $minValue,
            $maxValue
        ) {

            $minMaxAttrs = '';
            $minMaxHint = '';

            if ($minValue !== null || $maxValue !== null) {
                $minMaxAttrs = ($minValue !== null ? 'min="' . $minValue . '" ' : '') .
                    ($maxValue !== null ? 'max="' . $maxValue . '" ' : '');
                $minMaxHint = ($minValue !== null ? 'Min: ' . $minValue . ' ' : '') .
                    ($maxValue !== null ? 'Max: ' . $maxValue : '');
            }

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                <input type="{$type}" class="form-control {$extraClasses} {$requiredClass}" 
                       id="{$columnName}" name="{$columnName}" 
                       placeholder="{$placeholder}" {$requiredAttr} {$extraAttrs} {$minMaxAttrs}
                       value="{{ old('{$columnName}', \$data->{$columnName} ?? '') }}">
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}') || !empty('{$minMaxHint}'))
                    <small class="form-text text-muted">
                        {$description} {$minMaxHint}
                    </small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate textarea structure
        $generateTextAreaHtml = function ()
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $placeholder,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $description
        ) {

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                <textarea class="form-control {$requiredClass}" id="{$columnName}" 
                          name="{$columnName}" placeholder="{$placeholder}" {$requiredAttr}
                          rows="4">{{ old('{$columnName}', \$data->{$columnName} ?? '') }}</textarea>
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate Date/Time HTML structure
        $generateDateHtml = function ()
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $placeholder,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $columnType,
            $description
        ) {

            $inputClass = $columnType === 'date_time' ? 'js-datetimepicker' : 'js-datepicker';

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                <div class="input-group">
                    <input type="text" class="form-control {$inputClass} {$requiredClass}" 
                           id="{$columnName}" name="{$columnName}" 
                           placeholder="{$placeholder}" {$requiredAttr}
                           value="{{ old('{$columnName}', isset(\$data->{$columnName}) ? \$data->getRawOriginal('{$columnName}') : '' }}">
                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                </div>
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate Time HTML structure
        $generateTimeHtml = function ()
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $placeholder,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $description
        ) {

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                <div class="input-group">
                    <input type="text" class="form-control js-timepicker {$requiredClass}" 
                           id="{$columnName}" name="{$columnName}" 
                           placeholder="{$placeholder}" {$requiredAttr}
                           value="{{ old('{$columnName}', isset(\$data->{$columnName}) ? \$data->getRawOriginal('{$columnName}') : '' }}">
                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                </div>
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate Checkbox HTML structure
        $generateCheckboxHtml = function ()
        use ($columnWidth, $columnName, $columnTitle, $requiredIndicator, $isSwitch, $description) {

            $switchClass = $isSwitch == 'Y' ? 'form-switch' : '';

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <div class="form-check {$switchClass}">
                    <input type="hidden" name="{$columnName}" value="0">
                    <input class="form-check-input" type="checkbox" 
                           id="{$columnName}" name="{$columnName}" value="1"
                           @if(old("{$columnName}") || (isset(\$data->{$columnName}) && \$data->{$columnName} == 1)) checked @endif>
                    <label class="form-check-label" for="{$columnName}">
                        {$columnTitle} {$requiredIndicator}
                    </label>
                </div>
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate Select HTML structure
        $generateSelectHtml = function ($isSelect2 = false)
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $description,
            $sourceTable,
            $sourceTableColumnKey,
            $sourceTableColumnValue
        ) {

            $select2Class = $isSelect2 ? 'js-select2' : '';
            $snakeCaseTableName = $this->get_snake_case($sourceTable);
            $optionsHtml = '<option value="">Select ' . $columnTitle . '</option>';

            if ($sourceTable) {
                $optionsHtml .= '@foreach($' . $snakeCaseTableName . '_list as $list)';
                $optionsHtml .= '<option value="{{$list->' . $sourceTableColumnKey . '}}"';
                $optionsHtml .= '{{ old("' . $columnName . '", $data->' . $columnName . ' ?? "") == $list->' . $sourceTableColumnKey . ' ? "selected" : "" }}>';
                $optionsHtml .= '{{ $list->' . $sourceTableColumnValue . ' }}</option>';
                $optionsHtml .= '@endforeach';
            }

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                <select class="form-select {$select2Class} {$requiredClass}" 
                        id="{$columnName}" name="{$columnName}" {$requiredAttr}>
                    {$optionsHtml}
                </select>
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };


        // Helper function to generate Radio Button HTML structure
        $generateRadioHtml = function ()
        use ($columnWidth, $columnName, $columnTitle, $requiredIndicator, $requiredClass, $requiredAttr, $description, $sourceTable, $sourceTableColumnKey, $sourceTableColumnValue) {

            $optionsHtml = '';
            
            if ($sourceTable) {
                $snakeCaseTableName = $this->get_snake_case($sourceTable);
                $optionsHtml .= '@foreach($' . $snakeCaseTableName . '_list as $list)';
                $optionsHtml .= '<div class="form-check">';
                $optionsHtml .= '<input class="form-check-input" type="radio" name="' . $columnName . '" id="' . $columnName . '_{{ $loop->index }}" value="{{ $list->' . $sourceTableColumnKey . ' }}"';
                $optionsHtml .= '{{ old("' . $columnName . '", $data->' . $columnName . ' ?? "") == $list->' . $sourceTableColumnKey . ' ? "checked" : "" }} ' . $requiredAttr . '>';
                $optionsHtml .= '<label class="form-check-label" for="' . $columnName . '_{{ $loop->index }}">{{ $list->' . $sourceTableColumnValue . ' }}</label>';
                $optionsHtml .= '</div>';
                $optionsHtml .= '@endforeach';
            } else {
                // Fallback if no source table is specified
                $optionsHtml .= '<div class="form-check">';
                $optionsHtml .= '<input class="form-check-input" type="radio" name="' . $columnName . '" id="' . $columnName . '_1" value="1" ' . $requiredAttr . '>';
                $optionsHtml .= '<label class="form-check-label" for="' . $columnName . '_1">Option 1</label>';
                $optionsHtml .= '</div>';
                $optionsHtml .= '<div class="form-check">';
                $optionsHtml .= '<input class="form-check-input" type="radio" name="' . $columnName . '" id="' . $columnName . '_2" value="2" ' . $requiredAttr . '>';
                $optionsHtml .= '<label class="form-check-label" for="' . $columnName . '_2">Option 2</label>';
                $optionsHtml .= '</div>';
            }

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label class="form-label">{$columnTitle} {$requiredIndicator}</label>
                {$optionsHtml}
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
            </div>
        </div>
        HTML;
        };

        // Helper function to generate File Upload HTML structure
        $generateFileUploadHtml = function ($isImage = false)
        use (
            $columnWidth,
            $columnName,
            $columnTitle,
            $requiredIndicator,
            $requiredClass,
            $requiredAttr,
            $description
        ) {

            $accept = $isImage ? 'accept=".jpg,.jpeg,.png,.webp"' : '';
            $fileType = $isImage ? 'image' : 'file';

            return <<<HTML
        <div class="{$columnWidth} mb-3">
            <div class="form-group">
                <label for="{$columnName}" class="form-label">{$columnTitle} {$requiredIndicator}</label>
                
                @if(isset(\$data->{$columnName}) && \$data->{$columnName})
                    <div class="mb-2">
                        @if({$isImage})
                            <img src="{{ \$data->{$columnName} }}" class="img-thumbnail" style="max-height: 100px;">
                        @else
                            <a href="{{ \$data->{$columnName} }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View File
                            </a>
                        @endif
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" 
                                   name="delete_{$columnName}" id="delete_{$columnName}">
                            <label class="form-check-label" for="delete_{$columnName}">
                                Remove current {$fileType}
                            </label>
                        </div>
                    </div>
                @endif
                
                <input type="file" class="form-control {$requiredClass}" 
                       id="{$columnName}" name="{$columnName}" {$requiredAttr} {$accept}>
                       
                @if(\$errors->has('{$columnName}'))
                    <div class="invalid-feedback d-block">
                        {{ \$errors->first('{$columnName}') }}
                    </div>
                @endif
                @if(!empty('{$description}'))
                    <small class="form-text text-muted">{$description}</small>
                @endif
                
                <input type="hidden" name="current_{$columnName}" 
                       value="{{ \$data->getRawOriginal('{$columnName}') ?? '' }}">
            </div>
        </div>
        HTML;
        };

        // Generate the appropriate field based on input type
        switch ($inputType) {
            case 'text':
            case 'email':
            case 'number':
                return $generateInputHtml($inputType);

            case 'textarea':
                return $generateTextAreaHtml();

            case 'date_time':
                return $generateDateHtml();

            case 'time':
                return $generateTimeHtml();

            case 'select':
                return $generateSelectHtml();

            case 'select2':
                return $generateSelectHtml(true);

            case 'checkbox':
                return $generateCheckboxHtml();

            case 'image_upload':
                return $generateFileUploadHtml(true);
                

            case 'radio':
            return $generateRadioHtml();
            
            case 'file_upload':
                return $generateFileUploadHtml();

            default:
                return $generateInputHtml('text');
        }
    }

    private function generateController($pageId, $studlyCase, $tableName)
    {
        // Retrieve source_table values for the given page_id
        $formDetails = FormBuilder::where('page_id', $pageId)
            ->whereNotNull('source_table')
            ->pluck('source_table')
            ->toArray();

        // Convert the array to a comma-separated string
        $relatedTables = implode(',', $formDetails);

        $controllerDirectory = app_path("Http/Controllers/{$studlyCase}");
        $controllerPath = "{$controllerDirectory}/{$studlyCase}Controller.php";

        // Create the directory if it does not exist
        if (!File::exists($controllerDirectory)) {
            File::makeDirectory($controllerDirectory, 0755, true);
        }
        // Retrieve validation rules
        $validationRules = FormBuilder::where('page_id', $pageId)
            ->where('is_required', 'Y')
            ->pluck('is_required', 'column_name')
            ->toArray();

        // Initialize the controller content
        $controllerContent = "<?php\n\n";
        $controllerContent .= "namespace App\Http\Controllers\\{$studlyCase};\n\n";
        $controllerContent .= "use Illuminate\Http\Request;\n";
        $controllerContent .= "use App\Http\Controllers\Controller;\n";
        $controllerContent .= "use App\Models\\{$studlyCase}\\{$studlyCase};\n";
        $controllerContent .= "use Illuminate\Support\Facades\DB;\n";
        $controllerContent .= "use Illuminate\Support\Facades\Validator;\n\n";
        $controllerContent .= "class {$studlyCase}Controller extends Controller\n";
        $controllerContent .= "{\n";

        // Add the index method
        $controllerContent .= "    public function index()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        \${$tableName}_list = {$studlyCase}::all();\n";
        $controllerContent .= "        return view('{$tableName}.{$tableName}_list', compact('{$tableName}_list'));\n";
        $controllerContent .= "    }\n\n";

        // Add the create method
        $controllerContent .= "    public function create()\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        \$data = new {$studlyCase}();\n";

        // Initialize compact variables array
        $compactVariables = "'data'";

        if (!empty($relatedTables)) {
            foreach (explode(',', $relatedTables) as $table) {
                $table_new = Str::lower($this->get_snake_case($table));
                $controllerContent .= "        \${$table_new}_list = DB::table('{$table_new}')->get();\n";
                $compactVariables .= ", '{$table_new}_list'";
            }
        }

        $controllerContent .= "        return view('{$tableName}.{$tableName}_form', compact({$compactVariables}));\n";
        $controllerContent .= "    }\n\n";

        // Add the store method
        $controllerContent .= "    public function store(Request \$request)\n";
        $controllerContent .= "    {\n";
        if (!empty($validationRules)) {
            $controllerContent .= "        \$validator = Validator::make(\$request->all(), [\n";

            foreach ($validationRules as $field => $rule) {
                if ($rule) {
                    $controllerContent .= "            '{$field}' => 'required',\n";
                }
            }

            $controllerContent .= "        ]);\n\n";
            $controllerContent .= "        if (\$validator->fails()) {\n";
            $controllerContent .= "            return redirect()->back()->withErrors(\$validator)->withInput();\n";
            $controllerContent .= "        }\n\n";
        }
        $controllerContent .= "      {$studlyCase}::create(\$request->all()); \n";
        $controllerContent .= "     return redirect()->route('{$tableName}.index')->with('toast_success', '{$studlyCase} Created Successfully!');\n";
        $controllerContent .= "    }\n\n";

        // Add the show method
        $controllerContent .= "    public function show(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        // Your show logic here\n";
        $controllerContent .= "    }\n\n";

        // Add the edit method
        $controllerContent .= "    public function edit(\$id)\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        \$data = {$studlyCase}::findOrFail(\$id);\n";

        if (!empty($relatedTables)) {
            foreach (explode(',', $relatedTables) as $table) {
                $new_table = $this->get_snake_case($table);
                $controllerContent .= "        \${$new_table}_list = DB::table('{$new_table}')->get();\n";
            }
        }
        $controllerContent .= "        return view('{$tableName}.{$tableName}_form', compact({$compactVariables}));\n";
        $controllerContent .= "    }\n\n";

        // Add the update method
        $controllerContent .= "    public function update(Request \$request, {$studlyCase} \${$tableName})\n";
        $controllerContent .= "    {\n";
        if (!empty($validationRules)) {
            $controllerContent .= "        \$validator = Validator::make(\$request->all(), [\n";

            foreach ($validationRules as $field => $rule) {
                if ($rule) {
                    $controllerContent .= "            '{$field}' => 'required',\n";
                }
            }

            $controllerContent .= "        ]);\n\n";
            $controllerContent .= "        if (\$validator->fails()) {\n";
            $controllerContent .= "            return redirect()->back()->withErrors(\$validator)->withInput();\n";
            $controllerContent .= "        }\n\n";
        }
        $controllerContent .= "        \${$tableName}->update(\$request->all());\n";
        $controllerContent .= "     return redirect()->route('{$tableName}.index')->with('toast_success', '{$studlyCase} Updated Successfully!');\n";
        $controllerContent .= "    }\n\n";

        // Add the destroy method
        $controllerContent .= "    public function destroy({$studlyCase} \${$tableName})\n";
        $controllerContent .= "    {\n";
        $controllerContent .= "        \${$tableName}->delete();\n";
        $controllerContent .= "        return redirect()->route('{$tableName}.index')->with('toast_success', '{$studlyCase} Deleted Successfully!');\n";
        $controllerContent .= "    }\n";

        // End of the controller class
        $controllerContent .= "}\n";

        // Write the controller content to the file
        File::put($controllerPath, $controllerContent);
    }


    public function updateSortingOrder(Request $request)
    {
        $order = $request->input('order');

        DB::beginTransaction();

        try {
            // Get all elements at once for efficiency
            $elements = FormBuilder::where('page_id', $request->page_id)
                ->where('page_name', $request->page_name)
                ->get();

            // Update sorting order for all elements
            foreach ($order as $index => $id) {
                FormBuilder::where('id', $id)
                    ->update(['sorting_order' => $index]);
            }

            DB::commit();

            return response()->json([
                'toast_success' => 'Order updated successfully!',
                'status' => 200,
                'new_order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'toast_error' => 'Failed to update order',
                'status' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function deleteFormElement(Request $request)
    {
        $form_id = $request->form_id;
        FormBuilder::find($form_id)->delete();
        return response()->json(['msg' => 'Deleted Successfully!', 'status' => 200]);
    }

    public function getSourceTableColumns(Request $request)
    {
        $source_table = Str::lower(Str::snake($request->source_table));
        $source_table_columns = Schema::getColumnListing($source_table);

        // Filter out the excluded columns and transform the remaining columns
        $result = array_reduce($source_table_columns, function ($carry, $column) {
            if (!in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
                $carry[$column] = Str::title(str_replace('_', ' ', $column));
            }
            return $carry;
        }, []);

        return response()->json(['status' => 200, 'column_list' => $result]);
    }

    function get_studly_case($page_name)
    {
        return \App\Models\PageBuilder::where('page_name', $page_name)->first()->studly_case;
    }

    function get_snake_case($page_name)
    {
        return \App\Models\PageBuilder::where('page_name', $page_name)->first()->snake_case;
    }
}
