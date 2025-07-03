<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait DatabaseTrait
{
    public function setupDatabase($tableData): void
    {

        foreach ($tableData as $data) {

            // Create table if not exists
            if (!Schema::hasTable($data->table_name)) {
                Schema::create($data->table_name, function (Blueprint $table) {
                    $table->id();
                    $table->timestamps();
                    $table->softDeletes();
                    $table->integer('created_by')->nullable();
                    $table->integer('updated_by')->nullable();
                    $table->integer('deleted_by')->nullable();
                });
            } else {
                // Ensure the created_at, updated_at, and deleted_at columns exist
                Schema::table($data->table_name, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'created_at')) {
                        $table->timestamps();
                    }
                    if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                        $table->softDeletes();
                    }

                });


            }

            // Alter table to add or modify columns
            if (isset($data->table_columns) && count($data->table_columns) > 0) {
                foreach ($data->table_columns as $column_data) {
                    Schema::table($data->table_name, function (Blueprint $table) use ($column_data) {

                        if (!Schema::hasColumn($table->getTable(), $column_data->column_name)) {
                            // Add new column
                            $column = $table->{$column_data->column_type}($column_data->column_name, $column_data->column_length ?? null);
                            if (isset($column_data->column_default)) {
                                $column->default($column_data->column_default);
                            }
                            if ($column_data->is_nullable == 1) {
                                $column->nullable();
                            }
                            if ($column_data->is_unique == 1) {
                                $column->unique();
                            }
                            if ($column_data->is_unsigned == 1) {
                                $column->unsigned();
                            }
                            /*  if (isset($column_data->column_after)) {
                                  $column->after($column_data->column_after);
                              }*/
                        } else {
                            // Modify existing column if necessary
                            if ($column_data->is_nullable == 1) {
                                $table->string($column_data->column_name)->nullable()->change();
                            }
                        }
                    });
                }

                // Handle removed columns by making them nullable
                $existingColumns = Schema::getColumnListing($data->table_name);
                foreach ($existingColumns as $existingColumn) {
                    $found = false;
                    foreach ($data->table_columns as $column_data) {
                        if ($column_data->column_name == $existingColumn) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && !in_array($existingColumn, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                        Schema::table($data->table_name, function (Blueprint $table) use ($existingColumn) {
                            $table->string($existingColumn)->nullable()->change();
                        });
                    }
                }
            }
        }
    }
}
