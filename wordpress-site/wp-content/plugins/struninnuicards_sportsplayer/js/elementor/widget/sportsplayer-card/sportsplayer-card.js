class StruninnUICards_Elementor_Handler_SportsPlayer_Card extends elementorModules.frontend.handlers.Base {
  getDefaultElements() {
    const sportsPlayerCardElement = this.$element[0].querySelector('.struninnuicard-sportsplayer');

    return {
      sportsPlayerCard: sportsPlayerCardElement
    };
  }
}

window.addEventListener('elementor/frontend/init', () => {
  elementorFrontend.elementsHandler.attachHandler('StruninnUICards_Elementor_Widget_SportsPlayer_card_v1', StruninnUICards_Elementor_Handler_SportsPlayer_Card);
  elementorFrontend.elementsHandler.attachHandler('StruninnUICards_Elementor_Widget_SportsPlayer_card_v2', StruninnUICards_Elementor_Handler_SportsPlayer_Card);
  elementorFrontend.elementsHandler.attachHandler('StruninnUICards_Elementor_Widget_SportsPlayer_card_v3', StruninnUICards_Elementor_Handler_SportsPlayer_Card);
  elementorFrontend.elementsHandler.attachHandler('StruninnUICards_Elementor_Widget_SportsPlayer_card_v4', StruninnUICards_Elementor_Handler_SportsPlayer_Card);
});