module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		jshint: {
			// define the files to lint
			files: ['Gruntfile.js', 'js/app/**/*.js'],
			// configure JSHint
			options: {
				globals: {
					jQuery: true,
					console: true,
					module: true
				}
			}
		},
		watch: {
			files: ['<%= jshint.files %>'],
			tasks: ['jshint']
		},
		wiredep: {
			task: {
				// Point to the files that should be updated when running `grunt wiredep`
				src: [
					'main.html'
				],
				options: {}
			}
		},
		eslint: {
			options: {
				configFile: '.eslintrc.json'
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-wiredep');
	grunt.loadNpmTasks('validate-commit-message');

	// the default task can be run just by typing "grunt" on the command line
	grunt.registerTask('default', ['jshint']);
};
