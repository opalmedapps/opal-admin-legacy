module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-bump');

	grunt.initConfig({
	  bump: {
	    options: {
	      files: ['package.json'],
	      updateConfigs: [],
	      commit: false,
	      commitMessage: 'Release v%VERSION%',
	      commitFiles: ['package.json'],
	      createTag: false,
	      tagName: 'v%VERSION%',
	      tagMessage: 'Version %VERSION%',
	      push: false,
	      pushTo: 'upstream',
	      gitDescribeOptions: '--tags --always --abbrev=1 --dirty=-d',
	      globalReplace: false,
	      prereleaseName: 'rc',
	      metadata: '',
	      regExp: false
	    }
	  },
	});
};