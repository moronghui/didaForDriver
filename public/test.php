<!DOCTYPE html>
<html>
<head>
	<title>test</title>
<script type="text/javascript" src="jquery-1.8.2.min.js"></script>
</head>
<body>
<button id="test">test</button>

<script type="text/javascript">
	$(function(){
		/*$("#test").click(function(){
			$.ajax({
		 			type: 'get',
		 			url: "http://localhost/dida/public/Driver/start",
		 			dataType: 'html',
		 			data: {
		 				driverPosition: '125,125',
		 				driverPhone: '18826139825'
		 			},
		 			success: function(msgs) {
						alert(msgs);
					}
				});

		});	*/

		/*$("#test").click(function(){
			$.ajax({
		 			type: 'get',
		 			url: "http://localhost/dida/public/Driver/end",
		 			dataType: 'html',
		 			data: {
		 				driverPhone: '18826139825'
		 			},
		 			success: function(msgs) {
						alert(msgs);
					}
				});

		});*/

		/*$("#test").click(function(){
			$.ajax({
		 			type: 'get',
		 			url: "http://localhost/dida/public/Driver/pushOrder",
		 			dataType: 'html',
		 			data: {
		 				driverPhone: '18826139825'
		 			},
		 			success: function(msgs) {
						alert(msgs);
					}
				});

		});*/

		/*$("#test").click(function(){
			$.ajax({
		 			type: 'get',
		 			url: "http://localhost/dida/public/Driver/getOrder",
		 			dataType: 'html',
		 			data: {
		 				driverPhone: '18826139825',
		 				userPhone:'15218190853'
		 			},
		 			success: function(msgs) {
						alert(msgs);
					}
				});

		});*/

		$("#test").click(function(){
			$.ajax({
		 			type: 'get',
		 			url: "http://localhost/dida/public/Driver/finishOrder",
		 			dataType: 'html',
		 			data: {
		 				pathLength: '13',
		 				userPhone:'15218190853'
		 			},
		 			success: function(msgs) {
						alert(msgs);
					}
				});

		});		

	})
</script>
</body>

</html>