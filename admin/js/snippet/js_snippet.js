const jsSnippetHandlers = {
    console_log: () => insertSnippetV2(`console.log("@cursor@");`),
    alertjs: () => insertSnippetV2(`alert("@cursor@");`),
    document_ready: () => insertSnippetV2(`$(document).ready(function(){ \n    console.log("Document is ready!"); @cursor@ \n});`,4)
};
