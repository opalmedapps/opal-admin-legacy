var serviceAccount = require("./firebaseServiceAccountKey.json");
var admin = require("firebase-admin");

admin.initializeApp({
	credential: admin.credential.cert(serviceAccount),
	databaseURL: "https://brilliant-inferno-7679.firebaseio.com"
});

// Command line example: `node firebaseSetBlock.js --blocked=0 --uid=123456

// Grab blocked status from command line 
var blocked = process.argv[2].split("=")[1];
// Grab uid from command line
var uid = process.argv[3].split("=")[1];

if (blocked == 0) {
	admin.auth().updateUser(uid, {disabled: false})
		.then(function (userRecord) {
			process.exit(0);
		})
		.catch(function(error) {
			process.exit(1);
		});
} else if (blocked == 1){
	admin.auth().updateUser(uid, {disabled: true})
		.then(function (userRecord) {
			process.exit(0);
		})
		.catch(function(error) {
			process.exit(1);
		});
}
