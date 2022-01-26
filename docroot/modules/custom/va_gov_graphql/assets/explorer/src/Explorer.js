import React, {Component} from "react";
import GraphiQL from "graphiql";
import GraphiQLExplorer from "graphiql-explorer";

class Explorer extends Component {
  _graphiql: GraphiQL;

  constructor(props) {
    super();
    this.state = {
      schema: props.schema || undefined,
      query: props.query || undefined,
      explorerIsOpen: props.explorerIsOpen || true
    }
  }

  componentDidMount() {
    // sometimes the history is set on the editor and need be put in explorer
    this.setState({ query: this._graphiql.getQueryEditor().options.value });
  }

  _handleEditQuery = (query: string): void => this.setState({ query });

  _handleToggleExplorer = () => {
    this.setState({ explorerIsOpen: !this.state.explorerIsOpen });
  };

  render() {
    const { query, schema } = this.state;
    return (
      <div className="graphiql-container">
        <GraphiQLExplorer
          schema={schema}
          query={query}
          explorerIsOpen={this.state.explorerIsOpen}
          onEdit={this._handleEditQuery}
          onToggleExplorer={this._handleToggleExplorer}
        />
        <GraphiQL
          ref={ref => (this._graphiql = ref)}
          fetcher={this.props.fetcher}
          schema={schema}
          query={query}
          variables={this.props.variables }
          onEditQuery={this._handleEditQuery}
        >
          <GraphiQL.Toolbar>
            <GraphiQL.Button
              onClick={() => this._graphiql.handlePrettifyQuery()}
              label="Prettify"
              title="Prettify Query (Shift-Ctrl-P)"
            />
            <GraphiQL.Button
              onClick={() => this._graphiql.handleToggleHistory()}
              label="History"
              title="Show History"
            />
            <GraphiQL.Button
              onClick={this._handleToggleExplorer}
              label="Explorer"
              title="Toggle Explorer"
            />
          </GraphiQL.Toolbar>
        </GraphiQL>
      </div>
    );
  }
}

export default Explorer;
