<div style="margin-top: 12px; margin-bottom: -5px">
    <label class="free_label" for="company_name_input_empty_companies">
        Co. Name:
    </label>
    <input placeholder="Search by Company Name" id="company_name_input_empty_companies" type="text" maxlength="45" name="company_name_input_empty_companies" style="width: 220px;">
    <a target="_blank" class="button" href="/admin/default/generateletter?id=all" style="float: right; position: relative; top: -4px;">Print All</a>
</div>
<div id="empty_companies" class="grid-view">
    <table class="items mbot0">
        <thead>
        <tr>
            <th class="width280"><span>Company Name</span></th><th class="width50"><span>Printed</span></th><th><span></span></th></tr>
        </thead>
    </table>
    <div style="height: 400px; overflow: auto">
        <table class="items" id="empty_companies_grid">
            <tbody>
                <tr id="empty_company0"><td clospan="3">Enter Company Name to the text field to populate this grid</td></tr>
            </tbody>
        </table>
    </div>
</div>
