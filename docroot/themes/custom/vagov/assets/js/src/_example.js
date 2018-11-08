
(function ($, Drupal) {
    Drupal.behaviors.exampleBehavior = {
        attach:function (context, settings) {
            console.log('test from example behavior');
        }
    };
})(jQuery, Drupal);