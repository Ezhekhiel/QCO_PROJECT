<div class="tab-pane fade" id="output" role="tabpanel" aria-labelledby="output-tab">
    <h4 class="mb-3">
        <i class="bi bi-box-arrow-right me-2"></i> Form Output
    </h4>
    <p class="text-muted">Form Output dari Computer Stitching Centralize ke Cell</p>

    <div class="row d-flex justify-content-center">
        <div class="col-3 p-3 d-flex">
            <div class="p-2 card w-100">
                <div class="card m-2">
                    <div class="card-header">Logistik</div>
                    <div class="card-body">
                        <input type="text" class="form-control form-output" name="logistik" id="logistic_id_output"
                            placeholder="Input Logistik">
                    </div>
                </div>
                <div class="card m-2">
                    <div class="card-header">SHIFT</div>
                    <div class="card-body">
                        <select name="shift" id="shift_id_output" class="form-control form-output">
                            <option value="">Pilih Shift</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                </div>

                <div class="card m-2">
                    <div class="card-header">PO Information</div>
                    <div class="card-body">
                        <div class="m-1">
                            <label for="po_id_output" class="form-label">PO</label>
                            <input type="text" class="form-control form-output" name="po" id="po_id_output"
                                list="po_list_output" autocomplete="off" placeholder="Pilih PO"
                                onchange="change_data('output')" style="text-transform: uppercase;">
                            <datalist id="po_list_output"></datalist>
                        </div>
                        <div class="m-1">
                            <label for="wide_id_output" class="form-label">Wide</label>
                            <input type="text" readonly class="form-control form-output form_class" name="wide"
                                id="wide_id_output" autocomplete="off" list="wide_list_output"
                                onchange="change_data('output')">
                            <datalist id="wide_list_output"></datalist>
                        </div>
                        <div class="m-1">
                            <label for="cell_id_output" class="form-label">Cell</label>
                            <input type="text" readonly class="form-control form-output form_class" name="cell"
                                id="cell_id_output" autocomplete="off">
                        </div>
                        <div class="m-1">
                            <label for="style_id_output" class="form-label">Article / Style</label>
                            <input type="text" readonly class="form-control form-output" name="style"
                                id="style_id_output">
                        </div>
                        <div class="m-1">
                            <label for="qty_po_id_output" class="form-label">QTY PO</label>
                            <input type="number" readonly class="form-control form-output" name="qty_po"
                                id="qty_po_id_output">
                        </div>
                        <div class="m-1">
                            <label for="gender_id_output" class="form-label">Gender</label>
                            <input type="text" readonly class="form-control form-output" name="gender"
                                id="gender_id_output">
                        </div>
                        <div class="m-1">
                            <label for="date_id_output" class="form-label">Date</label>
                            <input type="date" class="form-control form-output" name="date" id="date_id_output"
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
                        <h5 class="fw-bold">SIZE OUTPUT</h5>
                    </div>
                    @for ($i = 1; $i <= 29; $i++)
                        @if (($i - 1) % 5 == 0)
                            <div class="col-2 text-center">
                        @endif

                        <div class="mb-1 form-group">
                            <label for="size_output_{{ $i }}"
                                class="col-form-label validation label_output_{{ $i }}">{{ $i }}</label>
                            <input type="text" class="form-control form-output validation_balance excel-cell"
                                data-type="output" autocomplete="off" id="size_output_{{ $i }}"
                                name="qty_output[]">
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
                        <label for="visual_output_{{ $i }}"
                            class="col-form-label validation label_output_{{ $i }}">{{ $i }}</label>
                        <input type="text" class="form-control form-output validation_balance"
                            onclick="openModal(1)" id="visual_output_{{ $i }}" readonly>
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
