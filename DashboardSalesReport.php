<?php
class DashboardSalesReport {
	public $settings = array(
		'name' => 'Dashboard Sales Report',
		'description' => 'Shows a graph of the last 12 months sales and expenses.',
	);
	function dashboard_submodule() {
		global $billic, $db;
		$year = date('Y');
		$month = date('n') + 1;
		$reports = array();
		for ($i = 1;$i >= - 10;$i--) {
			$month--;
			if ($month <= 0) {
				$month = (12 - $month);
				$year = ($year - 1);
			}
			$month_start = mktime(0, 0, 0, $month, 1, $year);
			$month_end = mktime(23, 59, 59, $month, date('t', $month_start) , $year);
			$month_sales = $db->q('SELECT SUM(`subtotal`) FROM `invoices` WHERE `status` = \'Paid\' AND `credit` = \'0\' AND `datepaid` > ? AND `datepaid` < ?', $month_start, $month_end);
			$month_sales = floor($month_sales[0]['SUM(`subtotal`)']);
			$month_expenditures = $db->q('SELECT SUM(`amount`) FROM `transactions` WHERE `amount` < \'0\' AND `date` > ? AND `date` < ?', $month_start, $month_end);
			$month_expenditures = floor($month_expenditures[0]['SUM(`amount`)']);
			$reports[$i]['date'] = date('Y/m/d', $month_start);
			$reports[$i]['sales'] = $month_sales;
			$reports[$i]['expenses'] = abs($month_expenditures);
		}
		$html = '<div id="graph-SalesReport" chartID="SalesReport" style="width: 100%; height:150px"></div><script>
addLoadEvent(function() {
g = new Dygraph(
      document.getElementById("graph-SalesReport"),
	  "Date,Sales,Expenses\n"+';
		$out = '';
		foreach ($reports as $r) {
			// 2009/07/12 12:34
			$out = '"' . $r['date'] . ',' . $r['sales'] . ',' . $r['expenses'] . '\n"+' . $out;
		}
		$html.= substr($out, 0, -1);
		$html.= ',
		{
			 axes: {
				y: {
					//drawAxis: false,
					//drawGrid: false,
					valueFormatter: function(x) {
						return \'' . get_config('billic_currency_prefix') . '\'+x.toFixed(2)+\'' . get_config('billic_currency_suffix') . '\';
					},
				},
				x: {
					//drawAxis: false,
					//drawGrid: false,
				}
			},
			interactionModel: {},
		}
    );
});
</script>';
		return array(
			'header' => 'Sales Report (12 Months)',
			'html' => $html,
		);
		/*
		addLoadEvent(function() {
		  var data = {
		      labels: [';
		      foreach ($months as $month) {
		          $html .= '"'.$month.'", ';
		      }
		      $html = substr($html, 0, -2);
		      $html .= '],
		      datasets: [';
		
		      $html .= '{label: "Sales",fillColor: "rgba(220,220,220,0.2)",strokeColor: "rgba(220,220,220,1)",pointColor: "rgba(220,220,220,1)",pointStrokeColor: "#fff",pointHighlightFill: "#fff",pointHighlightStroke: "rgba(220,220,220,1)",data: ['.implode(',', $sales).']}, ';
		      $html .= '{label: "Expenditures",fillColor: "rgba(110,110,110,0.2)",strokeColor: "rgba(110,110,110,1)",pointColor: "rgba(110,110,110,1)",pointStrokeColor: "#000",pointHighlightFill: "#000",pointHighlightStroke: "rgba(110,110,110,1)",data: ['.implode(',', $expenditures).']}, ';
		
		      $html = substr($html, 0, -2);
		      $html .= '
		      ]
		  };
		  var ctx = document.getElementById("graph-SalesReport").getContext("2d");
		  Charts["SalesReport"] = new Chart(ctx).Line(data, {
		      maintainAspectRatio: false,
		      scaleShowLabels: false,
		      pointHitDetectionRadius: 1,
		  });
		});
		</script>';
		      return array(
		          'header' => 'Sales Report (12 Months)',
		          'html' => $html,
		      );
		*/
		/*
			Sales Report
		
		$series_data = array(array('Month', 'Sales'));
		$year = date('Y');
		$month = date('n')+1;
		for ($i=1; $i>=-10; $i--) {
			$month--;
			if ($month<=0) {
				$month = (12-$month);
				$year = ($year-1);
			}
			$month_start = mktime(0, 0, 0, $month, 1, $year);
			$month_end = mktime(23, 59, 59, $month, date('t', $month_start), $year);
			$sales = $db->q('SELECT SUM(`subtotal`) FROM `invoices` WHERE `status` = \'Paid\' AND `credit` = \'0\' AND `datepaid` > ? AND `datepaid` < ?', $month_start, $month_end);
			$sales = floor($sales[0]['SUM(`subtotal`)']);
			$series_data[] = array(date('M y', $month_start), $sales);
		}
		$series_data = json_encode($series_data);
		
		echo '<table class="table table-striped">';
		echo '<tr><th colspan="2">Sales Report (12 Months)</th></tr>';
		echo '<tr><td><div id="graph-salesreport" style="height:200px"></div></tr>';
		echo '</table>';
		?>
		<script type="text/javascript">
		function DashboardSalesReport_init() {
		  if(google) {
		      google.load('visualization', '1.0', {
		          packages: ['corechart'],
		          callback: function() {
		              var data = google.visualization.arrayToDataTable(<?php echo $series_data; ?>);
		
		              var formatter = new google.visualization.NumberFormat(
		                   {negativeColor: 'red', negativeParens: true, pattern: '###,### <?php echo get_config('billic_currency_code'); ?>'});
		                  formatter.format(data, 1);
		
		              var options = {
		                  vAxis:{viewWindow: {min: 0}}
		              };
		
		              var chart = new google.visualization.ColumnChart(document.getElementById('graph-salesreport'));
		              chart.draw(data, options);
		          }
		      } );
		  }
		}
		addLoadEvent(DashboardSalesReport_init);
		</script>
		<?php
		*/
	}
}
