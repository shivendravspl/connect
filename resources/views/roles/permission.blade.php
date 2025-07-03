    @extends('layouts.app')
    @section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div
                    class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Master</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                            <li class="breadcrumb-item active">Permissions</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row" id="data-div">
            <div class="col-lg-12" id="divLanding">
                <div class="card">
                    <div class="card-body">
                        <div class="table-data">
                            <table class="table table-striped table-bordered">
                                @foreach($permissions as $permission => $value)
                                <tr>
                                    <th>{{ mb_strtoupper($permission) }}</th>
                                    <td style="white-space: normal">
                                        @foreach($value as $v)
                                        <span class="badge bg-primary-subtle text-primary">{{ $v }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                @endforeach
                            </table>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endsection