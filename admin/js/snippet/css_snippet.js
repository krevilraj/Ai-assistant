const cssSnippetHandlers = {
    default_css: () => insertSnippetV2(`body { margin: 0; padding: 0; font-family: Arial, sans-serif; @cursor@}`),
    margin_auto: () => insertSnippetV2(`.center-div { margin: auto; width: 50%;@cursor@ }`)
};
