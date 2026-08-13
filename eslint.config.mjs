import globals from 'globals';

// File patterns to ignore
const IGNORED_FILES = [
	'phpBB/assets/javascript/cropper.js',
	'phpBB/assets/javascript/hermite.js',
	'phpBB/assets/javascript/jquery-cropper.js',
	'phpBB/**/*.min.js',
	'phpBB/vendor/**/*.js',
	'phpBB/vendor-ext/**/*.js',
	'phpBB/phpbb/**/*.js',
	'phpBB/tests/**/*.js',
];

// ESLint rule configurations
const FORMATTING_RULES = {
	'quotes': ['error', 'single', { "allowTemplateLiterals": true }],
	'comma-dangle': ['error', 'always-multiline'],
	'block-spacing': 'error',
	'array-bracket-spacing': ['error', 'always'],
	'object-curly-spacing': ['error', 'always'],
	'space-before-function-paren': ['error', 'never'],
	'space-in-parens': 'off',
};

const CODE_QUALITY_RULES = {
	'semi': ['error', 'always'],
	'eqeqeq': ['error', 'always'],
	'curly': ['error', 'multi-line'],
	'no-var': 'error',
	'prefer-const': 'error',
	'no-console': 'off',
	'no-unused-vars': ['error', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
};

const DISABLED_STYLE_RULES = {
	'multiline-comment-style': 'off',
	'computed-property-spacing': 'off',
	'capitalized-comments': 'off',
	'no-lonely-if': 'off',
};

const mainConfig = {
	files: ['**/*.js', '**/*.js.twig'],
	linterOptions: {
		reportUnusedDisableDirectives: false,
	},
	languageOptions: {
		ecmaVersion: 'latest',
		sourceType: 'module',
		globals: {
			...globals.browser,
			...globals.node,
			...globals.jquery,
		},
	},
	rules: {
		...FORMATTING_RULES,
		...CODE_QUALITY_RULES,
		...DISABLED_STYLE_RULES,
	},
};

export default [
	{ ignores: IGNORED_FILES },
	mainConfig,
];
