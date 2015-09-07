<div id="company_name_row_user_assign">
    Client: Select a client
</div>
<div id="project_name_row_user_assign">
    Project: Select a project
</div>
<div id="user_name_row_user_assign">
    User: Select a user
</div>
<div class="clear"></div>
<div style="margin-top: 12px; margin-bottom: -5px">
    <label class="free_label" for="user_to_filter_last_name">
        Users:
    </label>
    <input placeholder="Search by Last Name" id="user_to_filter_last_name" type="text" maxlength="45" name="user_to_filter_last_name" style="width: 220px;">
    <button class="button" id="submit_user_assign">Assign User</button>
</div>
<div id="all_users" class="grid-view">
    <table class="items mbot0"">
        <thead>
        <tr>
            <th class="width200"><span>Name</span></th><th><span>Email</span></th></tr>
        </thead>
    </table>
    <div style="height: 250px; overflow: auto">
        <table class="items" id="all_users_grid">
            <tbody>
                <tr id="user0"><td clospan="3">Enter last name to the text field to populate this grid</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div style="height: 20px"></div>

<div id="users_to_delete" class="grid-view">
    <table class="items mbot0"">
    <thead>
    <tr>
        <th class="width200"><span>Name</span></th><th><span>Operation</span></th></tr>
    </thead>
    </table>
    <div style="height: 250px; overflow: auto">
        <table class="items" id="all_users_grid1">
            <tbody>
            <tr><td clospan="3">Select company and project to populate this grid</td></tr>
            </tbody>
        </table>
    </div>
</div>