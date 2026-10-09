import bpmnFlow from '../data/entity/bpmn_flow.json';
import bpmnIssue from '../data/entity/bpmn_issue.json';
import bpmnLane from '../data/entity/bpmn_lane.json';
import bpmnNode from '../data/entity/bpmn_node.json';
import bpmnPath from '../data/entity/bpmn_path.json';
import bpmnProcess from '../data/entity/bpmn_process.json';
import bpmnTaskRight from '../data/entity/bpmn_task_right.json';

type EntitySchema = { name: string };

export default function getGeneratedEntitySchemas(): Record<string, EntitySchema> {
  return {
    [bpmnFlow.name]: bpmnFlow,
    [bpmnIssue.name]: bpmnIssue,
    [bpmnLane.name]: bpmnLane,
    [bpmnNode.name]: bpmnNode,
    [bpmnPath.name]: bpmnPath,
    [bpmnProcess.name]: bpmnProcess,
    [bpmnTaskRight.name]: bpmnTaskRight,
  };
}
