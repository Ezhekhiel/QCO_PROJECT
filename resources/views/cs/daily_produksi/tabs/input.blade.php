<div class="tab-pane fade  @auth show active @endauth" id="input" role="tabpanel" aria-labelledby="input-tab">
    <h4 class="mb-3">
        <i class="bi bi-box-arrow-in-right me-2"></i> Form Input
    </h4>
    <p class="text-muted">Form Input dari Cell ke Computer Stitching Centralize</p>

    <div class="row d-flex justify-content-center">
        <div class="col-3 p-3 d-flex">
            <div class="p-2 card w-100">
                <div class="card m-2">
                    <div class="card-header">Logistik</div>
                    <div class="card-body">
                        <input type="text" class="form-control form-input" name="logistik" id="logistic_id_input"
                            placeholder="Input Logistik">
                    </div>
                </div>
                <div class="card m-2">
                    <div class="card-header">PO Information</div>
                    <div class="card-body">
                        <div class="m-1">
                            <label for="po_id_input" class="form-label">PO</label>
                            <input type="text" class="form-control form-input" name="po" id="po_id_input"
                                list="po_list" autocomplete="off" placeholder="Pilih PO" onchange="change_data('input')"
                                style="text-transform: uppercase;">
                            <datalist id="po_list"></datalist>
                        </div>
                        <div class="m-1">
                            <label for="wide_id_input" class="form-label">Wide</label>
                            <input type="text" readonly class="form-control form-input form_class" name="wide"
                                id="wide_id_input" autocomplete="off" onchange="change_data('input')"
                                list="wide_list_input">
                            <datalist id="wide_list_input"></datalist>
                        </div>
                        <div class="m-1">
                            <label for="cell_id_input" class="form-label">Cell</label>
                            <input type="text" readonly class="form-control form-input form_class" name="cell"
                                id="cell_id_input" autocomplete="off">
                        </div>
                        <div class="m-1">
                            <label for="style_id_input" class="form-label">Article / Style</label>
                            <input type="text" readonly class="form-control form-input" name="style"
                                id="style_id_input">
                        </div>
                        <div class="m-1">
                            <label for="qty_po_id_input" class="form-label">QTY PO</label>
                            <input type="number" readonly class="form-control form-input" name="qty_po"
                                id="qty_po_id_input">
                        </div>
                        <div class="m-1">
                            <label for="gender_id_input" class="form-label">Gender</label>
                            <input type="text" readonly class="form-control form-input" name="gender"
                                id="gender_id_input">
                        </div>
                        <div class="m-1">
                            <label for="date_id_input" class="form-label">Date</label>
                            <input type="date" class="form-control form-input" name="date" id="date_id_input"
                                value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-9 p-3">
            <div class="p-3 card w-100">
                <div class="row border m-2 p-2" style="font-size:82%">
                    <div class="col-12 text-center mt-2">
                        <h5 class="fw-bold">SIZE INCOMING</h5>
                    </div>
                    @for ($i = 1; $i <= 29; $i++)
                        @if (($i - 1) % 5 == 0)
                            <div class="col-2 text-center">
                        @endif

                        <div class="mb-1 form-group">
                            <label for="size_input_{{ $i }}"
                                class="col-form-label validation label_input_{{ $i }}">{{ $i }}</label>
                            <input type="text" class="form-control form-input validation_balance excel-cell"
                                autocomplete="off" id="size_input_{{ $i }}" data-type="input"
                                name="qty_input[]">
                        </div>

                        @if ($i % 5 == 0 || $i == 29)
                </div>
                @endif
                @endfor
            </div>

            <div class="row border m-2 p-2" style="font-size:82%">
                <div class="col-12 text-center mt-2">
                    <h5 class="fw-bold">BALANCE</h5>
                </div>
                @for ($i = 1; $i <= 29; $i++)
                    @if (($i - 1) % 5 == 0)
                        <div class="col-2 text-center">
                    @endif

                    <div class="mb-1 form-group">
                        <label for="visual_input_{{ $i }}"
                            class="col-form-label validation label_input_{{ $i }}">{{ $i }}</label>
                        <input type="text" class="form-control form-input validation_balance"
                            id="visual_input_{{ $i }}" readonly>
                    </div>

                    @if ($i % 5 == 0 || $i == 29)
            </div>
            @endif
            @endfor
        </div>
    </div>
</div>
</div>
</div>
