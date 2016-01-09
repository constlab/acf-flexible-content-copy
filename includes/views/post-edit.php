<script type="text/template" id="flexible-content-copy-template">
    <input type="search" name="search" placeholder="Search post by name"/>

    <table class="search-result wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th scope="col">Title</th>
            <th scope="col">Type</th>
            <th scope="col">Author</th>
            <th scope="col">Date</th>
            <th scope="col">Edit</th>
        </tr>
        <tr>
            <td colspan="5" class="status-text empty"></td>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

</script>

<script type="text/template" id="flexible-content-copy-row-template">
    <td><span class="post-title" title="<%= full %>"><%= title %></span></td>
    <td><%= type %></td>
    <td><%= author %></td>
    <td><%= date %></td>
    <td><a href="<%= link %>" target="_blank" title="Edit post"><span class="dashicons dashicons-edit"></span></a></td>
</script>

<script type="text/template" id="flexible-content-copy-detail-template">
    <div style="height: <%= height %>px;position: relative">
        <form action="<%= formUrl %>" method="post" class="post-detail-inner">
            <input type="hidden" name="dest_post" value="<?php the_ID() ?>">
            <input type="hidden" name="source_post" value="<%= id %>">
            <div>
                <h2><%= title %></h2>
                <a href="#" class="button button-close close button-large">Close</a>
                <br>

                <ol id="layouts-list"></ol>
            </div>
            <div class="post-detail-actions">
                <a href="#" class="button button-close close button-large">Close</a>
                <a href="#" class="button button-primary button-large">Paste</a>
            </div>
        </form>
    </div>
</script>

<div id="flexible-content-copy-dialog" style="display:none;"></div>

<div id="flexible-content-copy">
    <a href="#" class="open-dialog">Flexible Content copy</a>
</div>