<!DOCTYPE html><html>
<head>
<title>Recognition Test</title>
<script src="ex_lib/jquery.js"></script>
</head>
<style type="text/css">
#main_header{width:100%;display:flex;justify-content:center;align-items:center;padding:20px 0;font-family:sans-serif;font-size:20px;font-weight:bold;cursor:default;}
#main_line{width:100%;height:250px;display:flex;justify-content:center;align-items:center;margin-top:20px;margin-bottom:30px;}
.main_line_box{width:300px;height:100%;margin-right:20px;position:relative;overflow:hidden;background:#EFEFFA;border-radius:5px;}
.line_img{width:100%;height:100%;}
.line_img img{width:100%;height:100%;object-fit:contain;}
.line_controls{position:absolute;bottom:0;left:0;width:100%;height:40px;display:flex;justify-content:center;align-items:center;}
.line_controls button{width:100px;height:30px;cursor:pointer;}
#cam_stream{width:100%;height:100%;object-fit:contain;}
#cam_controls{position:absolute;bottom:0;left:0;width:100%;height:40px;display:flex;justify-content:center;align-items:center;}
#cam_controls button{width:100px;height:30px;cursor:pointer;margin-right:10px;}
#main_action{width:100%;height:40px;display:flex;justify-content:center;align-items:center;}
#main_action button{width:200px;height:30px;}
</style>
<body>
	<div id="main_header">Face Matcher Test - Facial Recognition API</div>
	<div id="main_line">
		<div class="main_line_box">
			<div class="line_img" id="image_one_holder"></div>
			<div class="line_controls">
				<button onclick="clear_frame('one')">clear</button>
			</div>
		</div>
		<div class="main_line_box">
			<video id="cam_stream" autoplay="true" muted="true"></video>
			<div id="cam_controls">
				<button id="cam_start" onclick="start_camera(this)">start camera</button>
				<button id="cam_take" onclick="capture_image(this)" style="display:none;">capture</button>
				<button id="cam_stop" onclick="stop_camera(this)" style="display:none;">stop camera</button>
			</div>
		</div>
		<div class="main_line_box">
			<div class="line_img" id="image_two_holder"></div>
			<div class="line_controls">
				<button onclick="clear_frame('two')">clear</button>
			</div>
		</div>
	</div>
	<div id="main_action">
		<button onclick="process_match(this)">Check Match</button>
		<i style="display:none;">processing result. please wait..</i>
	</div>
</body>
</html>
<script type="text/javascript">
var _stream = null;
var video_element = document.getElementById("cam_stream");
function process_match(element){
	var ia = $('#image_one_holder img').attr('src');
	var ib = $('#image_two_holder img').attr('src');
	if (ia==undefined||ib==undefined||ia==""||ib=="") return alert('Two images are required');
	$(element).hide();
	$(element).siblings('i').show();
	$.ajax({
		url:"http://localhost:2413/facial-recognition-api/matcher/",
		type:"POST",
		contentType:"application/json",
		data:JSON.stringify({'image_one':ia,'image_two':ib}),
		success:function(data){
			alert(data);
			$(element).show();
			$(element).siblings('i').hide();
		},
		error:function(){
			alert('Connection error. Retry');
			$(element).show();
			$(element).siblings('i').hide();
		}
	});
}
function clear_frame(t){
	if (t=="one") {
		$('#image_one_holder').html('');
	} else {
		$('#image_two_holder').html('');
	}
}
function start_camera(e){
	navigator.mediaDevices.getUserMedia({audio:false,video:true})
	.then(function(stream) {
		_stream  = stream;
		video_element.srcObject = stream;
		video_element.play();
		$('#cam_take,#cam_stop').show();
		$(e).hide();
	}).catch(function(err) {
		alert(err);
		$('#cam_take,#cam_stop').hide();
		$('#cam_start').show();
	});
}
function capture_image(e){
	video_element.pause();
	var i_holder = '';
	if ($('#image_one_holder img').length==0) {
		i_holder = document.getElementById('image_one_holder');
		$('#image_one_holder').html('');
	} else {
		i_holder = document.getElementById('image_two_holder');
		$('#image_two_holder').html('');
	}
	var i_image = document.createElement('img');
	i_holder.append(i_image);
	var i_canvas = document.createElement('canvas');
	var i_context = i_canvas.getContext("2d");
	i_canvas.width = video_element.videoWidth;
	i_canvas.height = video_element.videoHeight;
	i_context.drawImage(video_element,0,0,i_canvas.width, i_canvas.height);
	var imageData = i_canvas.toDataURL('image/png');
	i_image.setAttribute('src',imageData);
	video_element.play();
}
function stop_camera(e){
	if (_stream!=null) {
		_stream.getTracks().forEach(t => {
			t.stop();
		});
		$('#cam_take,#cam_stop').hide();
		$('#cam_start').show();
	}
}
</script>