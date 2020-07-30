//ES MODE
import canvas from 'canvas';
import faceapi from 'face-api.js';
import express from 'express';
import bodyParser from 'body-parser';

const app = express();
const { Canvas, Image, ImageData } = canvas;
faceapi.env.monkeyPatch({ Canvas, Image, ImageData });

//START APPLICATION
initialize_app();

//LOAD NEURAL MODELS ON APPLICATION DEPLOYMENT
function initialize_app(){
	Promise.all([
		faceapi.nets.ssdMobilenetv1.loadFromDisk('models'),
		faceapi.nets.tinyFaceDetector.loadFromDisk('models'),
		faceapi.nets.faceLandmark68Net.loadFromDisk('models'),
		faceapi.nets.faceLandmark68TinyNet.loadFromDisk('models'),
		faceapi.nets.faceRecognitionNet.loadFromDisk('models'),
		faceapi.nets.faceExpressionNet.loadFromDisk('models')
	]).then(ready_state);
}

//REQUEST HANDLERS
function ready_state(){
	//HEADERS FOR CROSS-SITE EASEABILITY
	app.use(function(req, res, next){
		res.setHeader('Access-Control-Allow-Origin','*');
		res.setHeader('Access-Control-Allow-Methods','GET, POST, OPTIONS, PUT, PATCH, DELETE');
		res.setHeader('Access-Control-Allow-Headers','X-Request-With,content-type');
		res.setHeader('Access-Control-Allow-Credentials',false);
		next();
	});

	//ALTER REQUEST PARSE SIZE DEPENDING ON YOUR PREFERENCES
	app.use(bodyParser.json({limit:'100mb'}));

	app.get('/facial-recognition-api/matcher/', (req, res) => {
		//OPTIONAL INFO ON GET REQUEST
		var info = JSON.stringify({
			'Title':'Face Matcher -Facial Recognition API',
			'Description':'A node.js based facial recognition for server side identity validations',
			'Author':'Paa Kwesi',
			'Version':'1.0.0'
		});
		res.send(info);
	});

	app.post('/facial-recognition-api/matcher/', (req, res) => {
		//FACE MATCHER GET 2 PRIMARY IMAGES VIA POST REQUEST
		var image_one = req.body.image_one;
		var image_two = req.body.image_two;

		//POST DATA VALIDATION
		if (image_one==undefined||image_two==undefined||image_one==""||image_two=="") {
			console.log('2 images are required');
			res.send('2 images are required');
			return;
		}
		
		//IMAGE OBJECT WITH CANVAS
		var img_one = new Image();
		img_one.src = image_one;
		var img_two = new Image();
		img_two.src = image_two;
					
		var person = 'unknown';

		//COMPARISON PROCESSING BASED ON IMAGE ONE'S MODELING AND DESCRIPTORS
		(async () => {
			const mod_results = await faceapi.detectAllFaces(img_one).withFaceLandmarks().withFaceDescriptors();
			if (!mod_results.length) {
				console.log('Facial match error');
				res.send('Facial match error');
			} else {
				const faceMatcher = new faceapi.FaceMatcher(mod_results);
				const singleResult = await faceapi.detectSingleFace(img_two).withFaceLandmarks().withFaceDescriptor();
				if (singleResult) {
					const bestMatch = faceMatcher.findBestMatch(singleResult.descriptor);
					person = bestMatch.toString();
					process_result(person,res);
				} else {
					console.log('Facial match error');
					res.send('Facial match error');
				}
			}
		})().catch(e=>{
			console.error(e);
		});

	});

	//ALTER PORT NUMBER TO YOUR PREFERED CHOICE
	const port = process.env.PORT || 2413;
	app.listen(port,()=> console.log(`Connection established to ${port}..`));
}

//VALIDATION RESPONSE HANDLER
function process_result(person,res){
	var trimmed_result = person.replace(/[^a-zA-Z]/g, '');
	if (trimmed_result=="person") {
		console.log("Facial match successfull");
		res.send("Facial match successfull");
	} else {
		console.log("Facial match error");
		res.send("Facial match error");
	}
}