{** 
 *	(c) 2024 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
<div id="{$identifier}" class="echart">
    <div class="header"
        style="display: flex; justify-content: center; font-size: 14px; font-weight: bold;margin-top: 10px;">
    </div>
    <div class="chart" style="height: 256px; width: 100%; "></div>
</div>
<script type="text/javascript">
    $(document).ready(function() {

        var options	= {$options};

        //options.seriesList = chart_convert_dates(options.seriesList);

        // Initialize the echarts instance based on the prepared dom
        var myChart = echarts.init(document.getElementById('{$identifier}').getElementsByClassName('chart')[0]);

        if (options.hasOwnProperty('clickAction')) {
            myChart.on('click', function(params) {
                // Print name in console
                //console.log(params.dataIndex, params.name, params.componentType, params.value);
                var bits = params.name.split(' - ');
                var start_date = new Date(Date.UTC(Number(bits[0]), Number(bits[1])-1, 1));
                var end_date = new Date(Date.UTC(bits[0], Number(bits[1]), 0));
                //console.log(start_date.toLocaleString(), end_date, bits);
                {literal}
                var url = options.clickAction.replace('{from}', start_date.toISOString().substr(0,10));
                var url = url.replace('{to}', end_date.toISOString().substr(0, 10));
                {/literal}
                window.location.assign(url);
            })
        };

        if(options.hasOwnProperty('header')) {
            var header = document.getElementById('{$identifier}').getElementsByClassName('header')[0];
            header.textContent = options.header.text;
        }

        var option = options.echart;

        myChart.setOption(option);
    });
</script>