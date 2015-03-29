var _x = {
	"deferred_jobs": {
		"map": "function( doc ) {  \n\tif ( doc.type == 'DreamFactory.Documents.WorkUnit' || doc.type == 'DreamFactory.Tools.Fabric.Components.WorkUnit' ) {\n\t\tif ( !doc.processed && !doc.in_flight  && 'defer' == doc.response ) {\n\t\t\temit([doc.queue, doc.created_at], doc._id);\n\t\t}\n\t}\n} "
	}
};
