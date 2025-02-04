{{-- Invest Log MODAL --}}
<div class="modal fade" id="investLogList" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog  modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title service-title">@lang('Information')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped ">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">@lang('Match Name')</th>
                        <th scope="col">@lang('Category Name')</th>
                        <th scope="col">@lang('Tournament Name ')</th>
                        <th scope="col">@lang('Question Name')</th>
                        <th scope="col">@lang('Option Name')</th>
                        <th scope="col">@lang('Ratio')</th>
                        <th scope="col">@lang('Result')</th>
                    </tr>
                    </thead>
                    <tbody class="result-body">

                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
            </div>
        </div>
    </div>
</div>

{{-- Refund MODAL --}}
<div id="refund" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="primary-header-modalLabel">@lang('Refund Amount')
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you want to this?')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                <form action="" method="post" class="refundRoute">
                    @csrf
                    @method('post')
                    <input type="hidden" name="betInvestId" value="" class="betInvestId">
                    <button type="submit" class="btn btn-soft-success">@lang('Yes')</button>
                </form>
            </div>
        </div>
    </div>
</div>
