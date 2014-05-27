{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
<div><canvas id="graph_{$name}" height="200" width="500"></canvas></div>
<script type="text/javascript">
{literal}
Ajax.Chart = Class.create();
Ajax.Chart.prototype = {
        getRemoteDatasetAndRender: function(url) {
                var xhr_options = {
                        method: 'get'                                
                }
                xhr_options.onComplete = function(xhr) {
                        var json = eval('('+xhr.responseText+')');
                        this.addDataset(json.dataset);
                        this.setOptions(Object.extend(this.options,json.options));
                        this.render();
                }.bind(this);
                new Ajax.Request(url,xhr_options);                
        }        
}
Object.extend(Plotr.BarChart.prototype,Ajax.Chart.prototype);
Object.extend(Plotr.LineChart.prototype,Ajax.Chart.prototype);
//we won't include the axis details in the options,
// as the remote call will provide them

var options = {
        padding: {left: 80, right: 20, top: 10, bottom: 30},
        backgroundColor: '#dbdbdb',
        colorScheme: 'blue'
};
{/literal}
var chart = new Plotr.BarChart('graph_{$name}',options);
//then just pass the URL to the function
chart.getRemoteDatasetAndRender('{$source}');
</script>