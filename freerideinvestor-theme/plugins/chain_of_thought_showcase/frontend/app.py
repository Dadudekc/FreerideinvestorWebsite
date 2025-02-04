import streamlit as st
import requests
import networkx as nx
import plotly.graph_objects as go
import os

# Load environment variables
BACKEND_URL = os.getenv("BACKEND_URL", "http://localhost:8000/solve")

st.set_page_config(page_title="Chain of Thought Reasoner Showcase", layout="wide")

st.title("🧠 Chain of Thought Reasoner Showcase")

st.markdown("""
    **Enter a task below**, and the ChainOfThoughtReasoner will decompose it into steps,
    execute them, and provide the final result along with a visualization of the reasoning process.
""")

def submit_task(task: str, agent_name: str):
    payload = {"task": task, "agent_name": agent_name}
    try:
        response = requests.post(BACKEND_URL, json=payload, timeout=60)
        response.raise_for_status()
        return response.json()
    except requests.exceptions.RequestException as e:
        st.error(f"An error occurred: {e}")
        return None

def visualize_graph(reasoning_graph: dict):
    G = nx.node_link_graph(reasoning_graph)
    
    if G.number_of_nodes() == 0:
        st.write("No reasoning steps to display.")
        return
    
    pos = nx.spring_layout(G, seed=42)
    
    # Create edge traces
    edge_x = []
    edge_y = []
    for edge in G.edges():
        x0, y0 = pos[edge[0]]
        x1, y1 = pos[edge[1]]
        edge_x += [x0, x1, None]
        edge_y += [y0, y1, None]
    
    edge_trace = go.Scatter(
        x=edge_x, y=edge_y,
        line=dict(width=2, color='#888'),
        hoverinfo='none',
        mode='lines')
    
    # Create node traces
    node_x = []
    node_y = []
    node_text = []
    for node, data in G.nodes(data=True):
        x, y = pos[node]
        node_x.append(x)
        node_y.append(y)
        node_text.append(data.get('content', ''))
    
    node_trace = go.Scatter(
        x=node_x, y=node_y,
        mode='markers+text',
        text=node_text,
        textposition="top center",
        hoverinfo='text',
        marker=dict(
            showscale=False,
            color='#1f77b4',
            size=20,
            line_width=2))
    
    # Create the figure
    fig = go.Figure(data=[edge_trace, node_trace],
                    layout=go.Layout(
                        title='Reasoning Steps',
                        titlefont_size=20,
                        showlegend=False,
                        hovermode='closest',
                        margin=dict(b=20,l=5,r=5,t=40),
                        annotations=[ dict(
                            text="",
                            showarrow=False,
                            xref="paper", yref="paper") ],
                        xaxis=dict(showgrid=False, zeroline=False, showticklabels=False),
                        yaxis=dict(showgrid=False, zeroline=False, showticklabels=False))
                    )
    
    st.plotly_chart(fig, use_container_width=True)

def main():
    with st.form(key='task_form'):
        task = st.text_area("📄 Enter your task:", height=150)
        agent_name = st.text_input("🤖 Agent Name:", value="default_agent")
        submit_button = st.form_submit_button(label='🔍 Solve Task')
    
    if submit_button:
        if not task.strip():
            st.warning("Please enter a valid task.")
        else:
            with st.spinner('Processing your task...'):
                result = submit_task(task, agent_name)
                if result:
                    st.success("✅ Task solved successfully!")
                    st.subheader("📈 Final Result")
                    st.write(result['final_result'])
                    
                    st.subheader("🔗 Reasoning Graph")
                    visualize_graph(result['reasoning_graph'])

if __name__ == "__main__":
    main()
