@extends('layouts.app')
@push('breadcrumb')
    <li class="breadcrumb-item active">User registration</li>
@endpush

@section('content')
     <div class="page-content-width-full">
            <div
                class="content-layout content-width-full user_registration-data-content js-ak-DataTable js-ak-delete-container js-ak-content-layout"
                data-id="user_registration"
                data-delete-modal-action="">
                <div class="content-element">
                    <div class="content-header">
                        <div class="header">
                            <h3>User registration List</h3>
                        </div>
                        <div class="action">
                            <div class="left">
                                <form class="search-container">
                                    <input name="user_registration_source" value="user_registration" type="hidden"/>
                                    <input name="user_registration_length" value="{{Request()->query("user_registration_length")}}" type="hidden"/>
                                    <div class="search">
                                        <input type="text" autocomplete="off" placeholder="Search" name="user_registration_search"
                                               value="{{(Request()->query("user_registration_source") == "user_registration")?Request()->query("user_registration_search")??"":""}}"
                                               class="form-input js-ak-search-input">
                                        <button class="search-button" draggable="false">
                                            @includeIf("layouts.icons.search_icon")
                                        </button>
                                        @if(Request()->query("user_registration_source") == "user_registration" && Request()->query("user_registration_search"))
                                            <div class="reset-search js-ak-reset-search">
                                                @includeIf("layouts.icons.reset_search_icon")
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            @can('add-UserRegistration')
                            <div class="right">
                                <a href="{{route('user_registration.create')}}" class="button primary-button add-new"
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
                                   <th>date</th>   <th>date</th>
                                <th class="no-sort manage-th" data-orderable="false">
                                    <div class="manage-links">

                                    </div>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="">
                                @foreach($user_registration_list as $data)
                                   <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ $data->date }}</td>
<td>{{ $data->date }}</td>

                                         <td class="manage-td">
                                            <div class="manage-links">
                                            @can('edit-UserRegistration')
                                                <a href="{{route('user_registration.edit',$data->id)}}" class="edit-link"
                                                   draggable="false">@includeIf("layouts.icons.edit_icon")</a>
                                            @endcan
                                            @can('delete-UserRegistration')
                                                <a data-link="{{route('user_registration.destroy',$data->id)}}"
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
