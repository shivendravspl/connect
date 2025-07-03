@extends('layouts.app')
@push('breadcrumb')
    <li class="breadcrumb-item active">distributor</li>
@endpush

@section('content')
     <div class="page-content-width-full">
            <div
                class="content-layout content-width-full distributor-data-content js-ak-DataTable js-ak-delete-container js-ak-content-layout"
                data-id="distributor"
                data-delete-modal-action="">
                <div class="content-element">
                    <div class="content-header">
                        <div class="header">
                            <h3>distributor List</h3>
                        </div>
                        <div class="action">
                            <div class="left">
                                <form class="search-container">
                                    <input name="distributor_source" value="distributor" type="hidden"/>
                                    <input name="distributor_length" value="{{Request()->query("distributor_length")}}" type="hidden"/>
                                    <div class="search">
                                        <input type="text" autocomplete="off" placeholder="Search" name="distributor_search"
                                               value="{{(Request()->query("distributor_source") == "distributor")?Request()->query("distributor_search")??"":""}}"
                                               class="form-input js-ak-search-input">
                                        <button class="search-button" draggable="false">
                                            @includeIf("layouts.icons.search_icon")
                                        </button>
                                        @if(Request()->query("distributor_source") == "distributor" && Request()->query("distributor_search"))
                                            <div class="reset-search js-ak-reset-search">
                                                @includeIf("layouts.icons.reset_search_icon")
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @can('add-Distributor')
                            <div class="right">
                                <a href="{{route('distributor.create')}}" class="button primary-button add-new"
                                   draggable="false">
                                    @includeIf("layouts.icons.add_new_icon")
                                    Add New
                                </a>
                            </div>
                            @endcan
                        </div>
                    </div>
                    <div class="content table-content">
                        <table class="table js-ak-content">
                            <thead>
                            <tr data-sort-method='thead'>
                                <th class="table-id" data-sort-method="number">ID</th>
                                   <th>date</th>   <th>name</th>
                                <th class="no-sort manage-th" data-orderable="false">
                                    <div class="manage-links">

                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="">
                                @foreach($distributor_list as $data)
                                   <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ $data->date }}</td>
<td>{{ $data->name }}</td>

                                         <td class="manage-td">
                                            <div class="manage-links">
                                            @can('edit-Distributor')
                                                <a href="{{route('distributor.edit',$data->id)}}" class="edit-link"
                                                   draggable="false">@includeIf("layouts.icons.edit_icon")</a>
                                            @endcan
                                            @can('delete-Distributor')
                                                <a data-link="{{route('distributor.destroy',$data->id)}}"
                                                   href="javascript:void(0);" data-id="{{$data->id}}"
                                                   class="delete-link js-ak-delete-link"
                                                   draggable="false">@includeIf("layouts.icons.delete_icon")</a>
                                            @endcan
                                            </div>
                                        </td>
                                   </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="content-footer">
                        <div class="left">
                            <div class="change-length js-ak-table-length-DataTable"></div>
                        </div>
                        <div class="right">
                            <div class="content-pagination">
                                <nav class="pagination-container">
                                    <div class="pagination-content">
                                        <div class="pagination-info js-ak-pagination-info"></div>
                                        <div class="pagination-box-data-table js-ak-pagination-box"></div>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            @includeIf("layouts.delete_modal_confirm")
        </div>
@endsection
