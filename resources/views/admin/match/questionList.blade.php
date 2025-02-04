@extends('admin.layouts.app')
@section('page_title', __('Question Lists'))
@section('content')
    <div id="optionListRender">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-end">
                    <div class="col-sm mb-2 mb-sm-0">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb breadcrumb-no-gutter">
                                <li class="breadcrumb-item">
                                    <a class="breadcrumb-link" href="javascript:void(0)">
                                        @lang('Dashboard')
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">@lang('Question Lists')</li>
                            </ol>
                        </nav>
                        <h1 class="page-header-title">@lang(optional($match->gameTeam1)->name) @lang('vs')
                            @lang(optional($match->gameTeam2)->name)-@lang($match->name)</h1>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    @if(adminAccessRoute(config('role.manage_game.access.add')))
                        <a href="{{route('admin.addQuestion',$match->id)}}"
                           class="btn btn-primary">@lang('Add Questions')</a>
                    @endif
                    @if(adminAccessRoute(config('role.manage_game.access.edit')))
                        <div class="dropdown text-right ms-2">
                            <button class="btn btn-sm  btn-dark dropdown-toggle" type="button" id="dropdownMenuButton"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class=""><i class="fas fa-bars pr-2"></i> @lang('Action')</span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                        data-bs-target="#all_active">@lang('Active')</button>
                                <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                        data-bs-target="#all_inactive">@lang('DeActive')</button>
                                <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                        data-bs-target="#all_close">@lang('Close')</button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th scope="col" class="text-center">
                                <input type="checkbox" class="form-check-input check-all tic-check" name="check-all"
                                       id="check-all">
                                <label for="check-all"></label>
                            </th>
                            <th>@lang('No.')</th>
                            <th>@lang('Name')</th>
                            <th>@lang('Status')</th>
                            @if(adminAccessRoute(config('role.manage_game.access.edit')))
                                <th>@lang('Locker')</th>
                                <th>@lang('Action')</th>
                            @endif
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($gameQuestions as  $item)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" id="chk-{{ $item->id }}"
                                           class="form-check-input row-tic tic-check" name="check" value="{{$item->id}}"
                                           data-id="{{ $item->id }}">
                                    <label for="chk-{{ $item->id }}"></label>
                                </td>
                                <td>{{ $loop->index + 1  }}</td>
                                <td>
                                    @lang($item->name)
                                </td>
                                <td>
                                    @if($item->status ==  0)
                                        <span class="badge bg-soft-warning text-warning">
                                                <span class="legend-indicator bg-warning"></span>@lang('Pending')
                                            </span>
                                    @elseif($item->status ==  1)
                                        <span class="badge bg-soft-success text-success">
                                                <span class="legend-indicator bg-success"></span>@lang('Active')
                                                </span>
                                    @elseif($item->status ==  2)
                                        <span class="badge bg-soft-danger text-danger">
                                                <span class="legend-indicator bg-danger"></span>@lang('Closed')
                                                </span>
                                    @endif
                                </td>
                                @if(adminAccessRoute(config('role.manage_game.access.edit')))
                                    <td data-label="@lang('Locker')">
                                        <a class="btn btn-white btn-sm'  {{($item->result == 1)?'disabled':'' }}"
                                           href="{{ route('admin.question.locker') }}"
                                           onclick="event.preventDefault();
                                           document.getElementById('locker{{$item->id}}').submit();">
                                            @if($item->is_unlock == 1)
                                                <i class="fa fa-unlock"></i>  {{ __('Unlock Now') }}
                                            @else
                                                <i class="fa fa-lock"></i> {{ __('Lock Now') }}
                                            @endif

                                        </a>
                                        <form id="locker{{$item->id}}" action="{{ route('admin.question.locker') }}"
                                              method="POST" class="d-none">
                                            @csrf
                                            <input type="text" name="question_id" value="{{$item->id}}">
                                        </form>

                                    </td>
                                    <td data-label="@lang('Action')">
                                        <button type="button"
                                                @click="getOptions({{$item}}, false)"
                                                class="btn btn-white btn-sm optionInfo"
                                                title="@lang('Option List')"
                                                data-bs-target="#optionList"
                                                data-bs-toggle="modal">
                                            <i class="fa fa-gamepad"
                                               aria-hidden="true"></i>
                                        </button>


                                        <button type="button" class="btn btn-white btn-sm editBtn"
                                                data-resource="{{$item}}"
                                                data-action="{{ route('admin.updateQuestion', $item->id) }}"
                                                data-bs-target="#editModal"
                                                data-bs-toggle="modal" data-backdrop="static"
                                                title="@lang('Edit Question')" {{($item->result == 1)?'disabled':'' }}>
                                            <i class="fa fa-edit"
                                               aria-hidden="true"></i>
                                        </button>


                                        <button type="button" class="btn btn-white btn-sm notiflix-confirm"
                                                data-bs-target="#delete-modal" data-backdrop="static"
                                                data-route="{{route('admin.deleteQuestion',$item->id)}}"
                                                data-bs-toggle="modal" title="@lang('Delete')">
                                            <i class="fa fa-trash-alt"
                                               aria-hidden="true"></i>
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="odd">
                                <td valign="top" colspan="8" class="dataTables_empty">
                                    <div class="text-center p-4">
                                        <img class="mb-3 dataTables-image"
                                             src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description"
                                             data-hs-theme-appearance="default">
                                        <img class="mb-3 dataTables-image"
                                             src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                             alt="Image Description" data-hs-theme-appearance="dark">
                                        <p class="mb-0">@lang("No data to show")</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @include('admin.match.partials.questionAttempt')

        <div class="modal fade" id="optionList" data-bs-backdrop="static" tabindex="-1" role="dialog"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title service-title">@{{ modalTitle }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <button class="btn btn-primary btn-sm mb-2" @click="addNewOption" type="button"><i
                                class="fa fa-plus-circle"></i> @lang('Add Option')</button>
                        <div class="table-responsive">
                            <table class="categories-show-table table table-hover table-striped table-bordered"
                                   id="zero_config">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">@lang('Name')</th>
                                    <th scope="col">@lang('Ratio')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="result-body" v-cloak>
                                <tr v-for="(item, index) in items">
                                    <th data-label="@lang('#')">#</th>
                                    <th data-label="@lang('Name')">@{{ item.option_name }}</th>
                                    <th data-label="@lang('Ratio')">@{{ item.ratio }}</th>
                                    <th data-label="@lang('Status')">
                                            <span v-if="item.status == '1'"
                                                  class="badge bg-soft-success text-success"><span
                                                    class="legend-indicator bg-success"></span> @lang('Active')</span>
                                        <span v-else-if="item.status == '2'"
                                              class="badge bg-soft-primary text-primary"><span
                                                class="legend-indicator bg-primary"></span> @lang('Win')</span>
                                        <span v-else-if="item.status == '0'"
                                              class="badge bg-soft-dark text-dark"><span
                                                class="legend-indicator bg-dark"></span> @lang('DeActive')</span>
                                        <span v-else-if="item.status == '-2'"
                                              class="badge bg-soft-danger text-danger"><span
                                                class="legend-indicator bg-danger"></span> @lang('Lost')</span>
                                        <span v-else-if="item.status == '3'"
                                              class="badge bg-soft-danger text-danger"><span
                                                class="legend-indicator bg-danger"></span> @lang('Refunded')</span>
                                    </th>
                                    <th data-label="@lang('Action')">
                                        <button type="button" @click="editOption(item, false)"
                                                class="btn btn-white btn-sm">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button type="button" @click="deleteOption(item, false)"
                                                class="btn btn-white btn-sm">
                                            <i class="fa fa-trash-alt"></i>
                                        </button>
                                    </th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" @click="closeOptionList(true)"
                                data-bs-dismiss="modal"><span>@lang('Close')</span></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="optionEdit" data-bs-backdrop="static" tabindex="-1" role="dialog"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog  modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title service-title">@lang('Edit Option')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" class="form-control" v-model="option.id">
                        <span class="text-danger" v-if="errorMsg && errorMsg.id">@{{ errorMsg.id[0] }}</span>

                        <div class="form-group">
                            <label>@lang('Option Name')</label>
                            <input type="text" class="form-control" v-model="option.option_name">

                            <span class="text-danger"
                                  v-if="errorMsg && errorMsg.option_name">@{{ errorMsg.option_name[0] }}</span>
                        </div>
                        <div class="form-group">
                            <label>@lang('Ratio')</label>
                            <input type="number" min="0" class="form-control" v-model="option.ratio">
                            <span class="text-danger" v-if="errorMsg && errorMsg.ratio">@{{ errorMsg.ratio[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>@lang('Status')</label>
                            <select class="form-control" v-model="option.status">
                                <option value="1" :selected="option.status == 1">Active</option>
                                <option value="0" :selected="option.status == 0">DeActive</option>
                            </select>
                            <span class="text-danger"
                                  v-if="errorMsg && errorMsg.status">@{{ errorMsg.status[0] }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" @click="closeOptionList(true)"
                                data-bs-dismiss="modal"><span>@lang('Close')</span></button>
                        <button type="button" class="btn btn-soft-success" @click="updateOption">
                            <span>@lang('Update')</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="optionAdd" tabindex="-1" role="dialog"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog  modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title service-title">@lang('Add New Option')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label>@lang('Option Name')</label>
                            <input type="text" class="form-control mt-2" v-model="option.option_name">

                            <span class="text-danger"
                                  v-if="errorMsg && errorMsg.option_name">@{{ errorMsg.option_name[0] }}</span>
                        </div>
                        <div class="form-group mb-3">
                            <label>@lang('Ratio')</label>
                            <input type="number" min="0" class="form-control mt-2" v-model="option.ratio">
                            <span class="text-danger" v-if="errorMsg && errorMsg.ratio">@{{ errorMsg.ratio[0] }}</span>
                        </div>

                        <div class="form-group">
                            <label>@lang('Status')</label>
                            <select class="form-control mt-2" v-model="option.status">
                                <option value="1" :selected="option.status == 1">Active</option>
                                <option value="0" :selected="option.status == 0">DeActive</option>
                            </select>
                            <span class="text-danger"
                                  v-if="errorMsg && errorMsg.status">@{{ errorMsg.status[0] }}</span>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" @click="closeOptionList(true)"
                                data-bs-dismiss="modal"><span>@lang('Close')</span></button>
                        <button type="button" class="btn btn-soft-success" @click="saveOption">
                            <span>@lang('Save')</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css-lib')
@endpush

@push('js-lib')
@endpush

@push('script')
    <script>
        'use strict';
        let optionListRender = new Vue({
            el: "#optionListRender",
            data: {
                question: {},
                currency: "{{basicControl()->currency_symbol}}",
                items: [],
                modalTitle: null,
                fetchDataOff: true,
                option: {},
                errorMsg: null,
            },
            mounted() {
            },
            methods: {
                closeOptionList(params, fetchDataOff) {
                    this.fetchDataOff = params
                },
                getOptions(question, fetchDataOff) {
                    var _this = this;
                    _this.question = question;
                    _this.modalTitle = question.name;
                    _this.fetchDataOff = fetchDataOff;
                    if (_this.fetchDataOff == true) {
                        return 0;
                    }
                    var $url = '{{route('admin.optionList')}}/' + question.id;
                    axios.get($url)
                        .then(function (response) {
                            _this.items = response.data
                        })
                        .catch(function (error) {
                            console.log(error);
                        })

                },
                editOption(obj, fetchDataOff) {
                    var _this = this;

                    _this.errorMsg = null;
                    _this.fetchDataOff = fetchDataOff;
                    if (_this.fetchDataOff == true) {
                        return 0;
                    }
                    $('#optionEdit').modal('show');
                    $('#optionEdit').modal({backdrop: 'static', keyboard: false})
                    _this.option = obj
                },
                deleteOption(obj, fetchDataOff) {
                    var _this = this;
                    axios.post('{{route('admin.optionDelete')}}', {
                        id: obj.id
                    })
                        .then(function (response) {
                            if (response.data.errors) {
                                _this.errorMsg = response.data.errors
                                return 0;
                            }
                            if (response.data.success == true) {

                                _this.items.splice(_this.items.indexOf(obj), 1);

                                Notiflix.Notify.Success("Delete Successfully");

                            }

                            if (response.data.success == false) {
                                Notiflix.Notify.Failure("" + response.data.result);
                            }


                        })
                        .catch(function (error) {

                        });
                },
                updateOption() {
                    var _this = this;
                    axios.post('{{route('admin.optionUpdate')}}', _this.option)
                        .then(function (response) {
                            if (response.data.errors) {
                                _this.errorMsg = response.data.errors
                                return 0;
                            }


                            if (response.data.result) {
                                _this.errorMsg = null;
                                var list = _this.items;
                                const result = list.map(function (obj) {
                                    if (obj.id == response.data.result.id) {
                                        obj = response.data.result
                                    }
                                    return obj
                                });
                                $('#optionEdit').modal('hide');
                                Notiflix.Notify.success("Updated Successfully");

                            } else {
                                $('#optionEdit').modal('hide');
                                Notiflix.Notify.failure("Result Over");

                            }
                        })
                        .catch(function (error) {

                        });

                },


                addNewOption() {
                    this.option.option_name = '';
                    this.option.ratio = 1;
                    this.option.status = 1;
                    this.option.match_id = this.question.match_id;
                    this.option.question_id = this.question.id;
                    $('#optionAdd').modal('show');
                    $('#optionAdd').modal({backdrop: 'static', keyboard: false})
                },

                saveOption() {
                    var _this = this;
                    axios.post('{{route('admin.optionAdd')}}', _this.option)
                        .then(function (response) {

                            if (response.data.errors) {
                                _this.errorMsg = response.data.errors
                                return 0;
                            }

                            if (response.data.result) {
                                _this.errorMsg = null;
                                var list = _this.items;

                                list.push(response.data.result);

                                Notiflix.Notify.success("Saved Successfully");
                                _this.option = {};
                                $('#optionAdd').modal('hide');
                                $('#optionList').modal('show');
                                $('#optionList').modal({backdrop: 'static', keyboard: false})
                                return 0;
                            }


                        })
                        .catch(function (error) {
                        });
                },


                formattedNumber(amount) {
                    var amount = (parseFloat(amount).toFixed(0))
                    return Intl.NumberFormat().format(amount);
                }
            }
        });
    </script>
@endpush



