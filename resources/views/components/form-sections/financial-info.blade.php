  <div id="financial-info" class="form-section">
      <h5 class="mb-4">Financial & Operational Information</h5>

      @php
      $financialInfo = $application->financialInfo ?? null;
      // Decode the JSON if it exists, otherwise use empty array
      $turnover = [];
      if (isset($financialInfo->annual_turnover)) {
      $turnover = json_decode($financialInfo->annual_turnover, true) ?? [];
      }
      $turnover = old('annual_turnover', $turnover);
      $defaultYears = ['2022-23', '2023-24', '2024-25'];

      // Ensure default years have empty values if not in turnover
      foreach ($defaultYears as $year) {
      if (!isset($turnover[$year])) {
      $turnover[$year] = '';
      }
      }
      @endphp

      <div class="row">
          <div class="col-md-6">
              <div class="form-group mb-3">
                  <label for="net_worth" class="form-label">Net Worth (Previous FY) *</label>
                  <input type="number" step="0.01" class="form-control" id="net_worth" name="net_worth" value="{{ old('net_worth', $financialInfo->net_worth ?? '') }}" required>
                  @error('net_worth')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group mb-3">
                  <label for="shop_ownership" class="form-label">Shop Ownership *</label>
                  <select class="form-control" id="shop_ownership" name="shop_ownership" required>
                      <option value="">Select Ownership</option>
                      <option value="owned" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'owned' ? 'selected' : '' }}>Owned</option>
                      <option value="rented" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'rented' ? 'selected' : '' }}>Rented</option>
                      <option value="lease" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'lease' ? 'selected' : '' }}>Lease</option>
                  </select>
                  @error('shop_ownership')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
              </div>
          </div>
      </div>

      <div class="row">
          <div class="col-md-6">
              <div class="form-group mb-3">
                  <label for="godown_area" class="form-label">Godown Area & Ownership *</label>
                  <input type="text" class="form-control" id="godown_area" name="godown_area" value="{{ old('godown_area', $financialInfo->godown_area ?? '') }}" required>
                  @error('godown_area')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
              </div>
          </div>
          <div class="col-md-6">
              <div class="form-group mb-3">
                  <label for="years_in_business" class="form-label">Years in Business *</label>
                  <input type="number" class="form-control" id="years_in_business" name="years_in_business" min="0" value="{{ old('years_in_business', $financialInfo->years_in_business ?? '') }}" required>
                  @error('years_in_business')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
              </div>
          </div>
      </div>

      <div class="row">
          <div class="col-md-12">
              <div class="form-group mb-3">
                  <label class="form-label">Annual Turnover (Enter at least one year) *</label>
                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th style="width: 40%;">Financial Year</th>
                              <th style="width: 60%;">Net Turnover (₹)</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach($defaultYears as $year)
                          <tr>
                              <td>
                                  FY {{ $year }}
                                  <input type="hidden" name="annual_turnover[year][]" value="{{ $year }}">
                              </td>
                              <td>
                                  <input type="number" step="0.01" class="form-control" name="annual_turnover[amount][{{ $year }}]" value="{{ old("annual_turnover.amount.$year", $turnover[$year] ?? '') }}" placeholder="Enter turnover (₹)" aria-describedby="turnover_error_{{ $year }}">
                                  @error("annual_turnover.amount.$year")
                                  <div id="turnover_error_{{ $year }}" class="text-danger">{{ $message }}</div>
                                  @enderror
                              </td>
                          </tr>
                          @endforeach
                      </tbody>
                  </table>
                  @error('annual_turnover')
                  <div class="text-danger">{{ $message }}</div>
                  @enderror
              </div>
          </div>
      </div>
  </div>