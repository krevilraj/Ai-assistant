const jsSnippetHandlers = {
    console_log: () => insertSnippet(`console.log("");`,3),
    alertjs: () => insertSnippet(`alert("");`,3),
    document_ready: () => insertSnippet(`$(document).ready(function(){ \n    console.log("Document is ready!"); \n});`,4)
};
