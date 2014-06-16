$(function() {
	/**
	 * IEの場合、console.logがundefinedとなることによる対策
	 */
	if (!window.console) {
		window.console = {
			log : function(msg) {
				// do nothing.
			}
		};
	}
	
	/**
	 * JavaScriptからVizualizerの機能を呼び出すためのクラスです。
	 */
	Vizualizer = function() {

	};

	/**
	 * プロパティを記述
	 */

	/**
	 * JSONの呼び出しを行う
	 */

})
