// eslint.config.mjs

// Use Promise.all for parallel imports to improve loading performance
const [
	{ default: globalsAll },
	{ default: unicorn },
	{ default: importPlugin },
	{ default: nodePlugin },
	{ default: promisePlugin },
] = await Promise.all([
	import('globals'),
	import('eslint-plugin-unicorn'),
	import('eslint-plugin-import'),
	import('eslint-plugin-n'),
	import('eslint-plugin-promise'),
]);

export default [
	// 🔒 Global ignore block — applies BEFORE any parsing
	{
		ignores: [
			'phpBB/assets/javascript/cropper.js',
			'phpBB/assets/javascript/hermite.js',
			'phpBB/assets/javascript/jquery-cropper.js',
			'phpBB/ext/**/*.js',
			'phpBB/**/*.min.js',
			'phpBB/vendor/**/*.js',
			'phpBB/vendor-ext/**/*.js',
			'phpBB/phpbb/**/*.js',
			'phpBB/tests/**/*.js',
		],
	},

	// 🌐 Main config for your source files
	{
		files: ['**/*.js'],
		linterOptions: {
			reportUnusedDisableDirectives: false,
		},
		languageOptions: {
			ecmaVersion: 'latest',
			sourceType: 'module',
			globals: {
				...globalsAll.browser,
				...globalsAll.node,
				...globalsAll.jquery,
			},
		},
		plugins: {
			unicorn,
			import: importPlugin,
			n: nodePlugin,
			promise: promisePlugin,
		},
		rules: {
			// Your personal customizations
			'quotes': ['error', 'single'],
			'comma-dangle': ['error', 'always-multiline'],
			'block-spacing': 'error',
			'array-bracket-spacing': ['error', 'always'],
			'multiline-comment-style': 'off',
			'computed-property-spacing': 'off',
			'space-before-function-paren': ['error', 'never'],
			'space-in-parens': 'off',
			'capitalized-comments': 'off',
			'object-curly-spacing': ['error', 'always'],
			'no-lonely-if': 'off',
			'unicorn/prefer-module': 'off',

			// XO-inspired defaults
			'semi': ['error', 'always'],
			'eqeqeq': ['error', 'always'],
			'curly': ['error', 'multi-line'],
			'no-var': 'error',
			'prefer-const': 'error',
			'no-console': 'off',
			'no-unused-vars': ['error', { argsIgnorePattern: '^_', varsIgnorePattern: '^_' }],
			'unicorn/filename-case': ['off', { case: 'kebabCase' }],
			'unicorn/no-null': 'off',
			'unicorn/prefer-ternary': 'off',
			'unicorn/no-array-for-each': 'off',
			'import/order': ['error', { 'newlines-between': 'always' }],
			'n/no-missing-import': 'error',
			'promise/always-return': 'off',
		},
	},
];
