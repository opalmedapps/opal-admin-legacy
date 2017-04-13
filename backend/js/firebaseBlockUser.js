var config = require("./../../js/config.js");
var serviceAccount = require("./../../js/firebaseServiceAccountKey.json");
var admin = require("firebase-admin");

admin.initializeApp({
	credential: admin.credential.cert(serviceAccount),
	databaseURL: "https://brilliant-inferno-7679.firebaseio.com"
});

// Grab uid from command line 
var uid = process.argv[2];

//var uid = "05a53469-5cb5-4a82-927f-4f8b70f33ed6";
admin.auth().updateUser(uid, {disabled: true})
	.then(function (userRecord) {
		return 1;
	})
	.catch(function(error) {
		return -1;
	});