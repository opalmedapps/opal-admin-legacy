/* 
 * Javascript global settings:
 */

// Absolute path of this package (include trailing slash)
var ABSPATH = "ABSPATH_HERE"; 
// URL path of this package starting at http baseURL (include trailing slash)
var URLPATH = "URLPATH_HERE"; 

// Other global JS configs...
var firebaseConfig = {
	apiKey: 'API_KEY_HERE',
	authDomain: 'AUTH_DOMAIN_HERE',
	databaseURL: 'DATABASE_URL_HERE',
	storageBucket: 'STORAGE_BUCKET_HERE',
	messagingSenderId: 'MESSAGING_SENDER_ID_HERE'
};

// firebaseConfig is a global variable from config.js
var FB = firebase.initializeApp(firebaseConfig);

var INSTALL_ACCESS = true; // Put false after successful installation
