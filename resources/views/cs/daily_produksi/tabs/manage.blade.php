  <div class="tab-pane fade" id="manage" role="tabpanel" aria-labelledby="manage-tab">
      <div class="p-3 col-2">
          <label for="search_date_manage" class="form-label">Cari Berdasarkan Tanggal</label>
          <input type="date" class="form-control" name="date" onchange="main_manage()" id="search_date_manage"
              value="{{ date('Y-m-d') }}">
      </div>
      <div class="p-3 row">

          <div class="col-6">
              <h4 class="mb-3">
                  <i class="bi bi-box-arrow-in-down me-2"></i> Data Input
              </h4>
              <p class="text-muted">Data Input Computer Stitching Centralized</p>
              <div class="p-2 card">
                  <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                      <table class="table table-hover align-middle text-center">

                          <thead class="table-light">

                              <tr>
                                  <th>No</th>
                                  <th>PO</th>
                                  <th>Wide</th>
                                  <th>Size</th>
                                  <th>QTY</th>
                                  <th class="bg-secondary text-light" style="width: 10%">Action</th>
                              </tr>

                          </thead>

                          <tbody id="tbody_input">
                          </tbody>

                      </table>

                  </div>
              </div>
          </div>
          <div class="col-6">
              <h4 class="mb-3">
                  <i class="bi bi-box-arrow-in-up me-2"></i> Data Output
              </h4>
              <p class="text-muted">Data Output Computer Stitching Centralized</p>
              <div class="p-2 card">
                  <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">

                      <table class="table table-hover align-middle text-center">

                          <thead class="table-light">

                              <tr>
                                  <th>No</th>
                                  <th>PO</th>
                                  <th>Wide</th>
                                  <th>Shift</th>
                                  <th>Size</th>
                                  <th>QTY</th>
                                  <th class="bg-secondary text-light" style="width: 10%">Action</th>
                              </tr>

                          </thead>

                          <tbody id="tbody_output">
                          </tbody>

                      </table>

                  </div>
              </div>
          </div>
      </div>
  </div>
