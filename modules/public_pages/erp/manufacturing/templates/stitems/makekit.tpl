{content_wrapper}
    {form controller="stitems" action="produceitemkit"}
    {input type='hidden'  attribute='id' value=$stitem_id}
    <div id="view_page" class="clearfix">
        <dl class="float-left" >
            {input type='text' attribute='quantity' name='quantity' label='Quantity to produce' class="compulsory" }
        </dl>
        <dl>
            <dt></dt>
            <dd><p class="help-text">Note: the materials required to produce this kit will be backflushed.</dd>
            {submit value='Produce Kit'}
        </dl>
    </div>
    {/form}
{/content_wrapper}