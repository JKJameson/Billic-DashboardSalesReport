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
			$month_sales = abs(floor($month_sales[0]['SUM(`subtotal`)']));
			$month_expenditures = $db->q('SELECT SUM(`amount`) FROM `transactions` WHERE `amount` < \'0\' AND `date` > ? AND `date` < ?', $month_start, $month_end);
			$month_expenditures = abs(floor($month_expenditures[0]['SUM(`amount`)']));
			$month_wages = $db->q('SELECT SUM(`amount`) FROM `transactions` WHERE (LOWER(`description`) LIKE \'% wage\' OR LOWER(`description`) LIKE \'wage %\') AND `amount` < \'0\' AND `date` > ? AND `date` < ?', $month_start, $month_end);
			$month_wages = abs(floor($month_wages[0]['SUM(`amount`)']));
			$reports[$i]['date'] = date('Y/m/d', $month_start);
			$reports[$i]['sales'] = $month_sales;
			$reports[$i]['expenses'] = $month_expenditures-$month_wages;
			if ($month_wages > 0) {
				$reports[$i]['wages'] = $month_wages;	
			}
			$reports[$i]['profit'] = $month_sales - $month_expenditures;
		}
		$html = '<div id="graph-SalesReport" chartID="SalesReport" style="width: 100%; height:150px"></div><script>
addLoadEvent(function() {
g = new Dygraph(
      document.getElementById("graph-SalesReport"),
	  "Date,Profit,Sales,Expenses,Wages\n"+';
		$out = '';
		foreach ($reports as $r) {
			// 2009/07/12 12:34
			$out = '"' . $r['date'] . ',' . $r['profit'] . ',' . $r['sales'] . ',' . $r['expenses'] . ',' . $r['wages'] . '\n"+' . $out;
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
	}
}
