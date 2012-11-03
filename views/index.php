<?php 
$this->load->view('errors'); 
$tmpl = array (
	'table_open'          => '<table class="mainTable" border="0" cellspacing="0" cellpadding="0">',

	'row_start'           => '<tr class="even">',
	'row_end'             => '</tr>',
	'cell_start'          => '<td style="width:50%;">',
	'cell_end'            => '</td>',

	'row_alt_start'       => '<tr class="odd">',
	'row_alt_end'         => '</tr>',
	'cell_alt_start'      => '<td>',
	'cell_alt_end'        => '</td>',

	'table_close'         => '</table>'
);

$this->table->set_template($tmpl); 
$this->table->set_empty("&nbsp;");
?>
<table width="100%">
	<tr>
		<td width="50%">
		<?php 
		$this->table->set_heading(lang('store_overview'),' ');
		$this->table->add_row(lang('total_sales'), m62_format_money($total_sales));
		$this->table->add_row(lang('this_years_sales'), m62_format_money($this_years_sales));
		$this->table->add_row(lang('average_order'), m62_format_money($average_order));
		$this->table->add_row(lang('this_years_orders'), $this_years_orders);
		$this->table->add_row(lang('this_months_orders'), '<a href="'.$url_base.'history_report'.AMP.'month='.date('m').AMP.'year='.date('Y').'">'.$this_months_orders.'</a>');
		$this->table->add_row(lang('todays_orders'), count($todays_orders));		
		$this->table->add_row(lang('total_orders'), '<a href="'.$url_base.'orders">'.$total_successful_orders.' ('.$total_orders.')</a>');
		$this->table->add_row(lang('total_customers'), '<a href="'.$url_base.'customers">'.$total_customers.'</a>');
		
		echo $this->table->generate();
		// Clear out of the next one
		$this->table->clear();		
		?>
		</td>
		<td valign="top">
		<?php 
		$this->table->set_heading(lang('recent_sales'));
		if(count($chart_order_history) >= '1')
		{
			$this->table->add_row('<div id="chart_div"></div>');
		}
		else
		{
			$this->table->add_row(lang('no_data_to_plot'));
		}
		echo $this->table->generate();
		// Clear out of the next one
		$this->table->clear();		
		?>		
		</td>
	</tr>
</table>
<?php 		
if(count($chart_order_history) >= '1'):
?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Year');
        data.addColumn('number', 'Totals');
        data.addColumn('number', 'Subtotals');
        data.addRows(<?php echo count($chart_order_history);?>);
        <?php 
        $i = 0;
        foreach($chart_order_history AS $date)
        { 
        	echo "data.setValue($i, 0, '".m62_convert_timestamp(strtotime($date['order_date']), $settings['graph_date_format'])."');";
        	echo "data.setValue($i, 1, ".$date['total'].");";
        	echo "data.setValue($i, 2, ".$date['subtotal'].");";
        	$i++;
        }
        ?>

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
		var chart_width = document.getElementById("chart_div").offsetWidth+10;
		var area_width = chart_width+20;
		//alert(chart_width);

        var formatter = new google.visualization.NumberFormat({prefix: '<?php echo $number_prefix; ?>', negativeColor: 'red', negativeParens: true});
        formatter.format(data, 1);
        formatter.format(data, 2);	        
        chart.draw(data, {
            width: chart_width, 
            height: 208, 
            legend:'in', 
            select: 'myClickHandler',
            hAxis: {slantedText: true},
            backgroundColor: 'none',
            chartArea: {
            	width: area_width, 
            	height: "160",
            	top: 10,
            	left:30
            }           
		});


        // a click handler which grabs some values then redirects the page
        google.visualization.events.addListener(chart, 'select', function() {
          // grab a few details before redirecting
          var selection = chart.getSelection();
          var row = selection[0].row;
          var col = selection[0].column;
          var date = data.getValue(row, 0);
          //location.href = '<?php echo html_entity_decode($url_base.'history_report'.AMP); ?>date=' + date;
        });		

        //google.visualization.events.addListener(chart, 'select', myClickHandler);
      }

      function myClickHandler(){

    	  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
    	  var selection = chart.getSelection();

    	  for (var i = 0; i < selection.length; i++) {
    	    var item = selection[i];
    	    if (item.row != null && item.column != null) {
    	      message += '{row:' + item.row + ',column:' + item.column + '}';
    	    } else if (item.row != null) {
    	      message += '{row:' + item.row + '}';
    	    } else if (item.column != null) {
    	      message += '{column:' + item.column + '}';
    	    }
    	  }
    	  if (message == '') {
    	    message = 'nothing';
    	  }
    	  alert('You selected ' + message);
    	}
    </script>
<?php 
else:

?>
Nothing to report :(

<?php 

endif; 

?>

<div id="my_accordion">
	<h3 class="accordion"><?=lang('todays_orders')?></h3>
	<div id="todays_orders">
	
	<?php 
	if(count($todays_orders) > 0)
	{
	
		echo form_open($query_base.'delete_order_confirm'); 
		
		$this->table->set_template($cp_pad_table_template);
		$this->table->set_heading(
			lang('order_id').'/'.lang('edit'),
			lang('customer_name'),
			lang('status'),
			lang('order_date'),
			lang('total'),
			''
		);
	
		foreach($todays_orders as $order)
		{
			$toggle = array(
					  'name'		=> 'toggle[]',
					  'id'		=> 'edit_box_'.$order['entry_id'],
					  'value'		=> $order['entry_id'],
					  'class'		=>'toggle'
					  );
			
			$packingslip_url = m62_get_invoice_url($order['entry_id'], TRUE);
			$customer_link = 'customer_view&email=';
			$this->table->add_row(
									'<a href="'.$url_base.'order_view'.AMP.'id='.$order['entry_id'].'">'.$order['title'].'</a>',
									'<a href="'.$url_base.'customer_view'.AMP.'email='.$order['email'].'">'.$order['first_name'].' '.$order['last_name'].'</a>',
									'<span style="color:#'.m62_status_color($order['status'], $order_channel_statuses).'">'.lang($order['status']).'</span>',
									m62_convert_timestamp($order['entry_date']),
									$number_format_defaults_prefix.$order['order_total'],
									'<a class="nav_button" href="'.$packingslip_url.'" target="_blank">'.lang('packing_slip').'</a>'
									);
		}
		
		echo $this->table->generate();
		echo '</form>';
	?>
	
	
	<?php } else { ?>
	<?php echo lang('no_matching_orders')?>
	<?php } ?>
	</div>




</div>